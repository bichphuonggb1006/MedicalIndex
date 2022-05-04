<?php

namespace Company\SQL;

use Company\Cache\Cache;
use Company\Entity\Entity;
use Exception;


abstract class Mapper extends Query
{

    static protected $instance;

    /** @var \ADOConnection */
    public $db;
    protected $paramsWhere = [];
    protected $paramsJoin = [];
    protected $pageSize = 20;
    protected $mapperSet;
    protected $comments = [];
    protected $transactionErrors = [];
    protected $pageNo;

    /**
     * Cache lifetime in second, false=no cache
     * @var int
     */
    protected $queryCacheLifetime = false;

    function __construct()
    {
        $this->db = $this->getConnection();
        $this->select($this->tableAlias() . '.*')->from($this->tableName() . ' ' . $this->tableAlias());
    }

    /**
     * Cho phép override instance của Mapper
     * @param \Company\SQL\Mapper $instance
     */
    static function setInstance(Mapper $instance)
    {
        //do Mapper được kế thừa rất nhiều nên dùng self để độc lập các thành phần
        static::$instance = $instance;
    }

    /**
     * @return static
     */
    static function makeInstance()
    {
        if (self::$instance) {
            $class = get_class(static::$instance);
            return new $class;
        } else {
            return new static;
        }
    }

    abstract function tableName();

    abstract function tableAlias();

    /**
     * Gọi đúng DB connection
     */
    function getConnection()
    {
        return DB::getInstance();
    }

    /**
     * Tên trường khóa chính, default = id
     */
    function getPkField()
    {
        return 'id';
    }

    function makeEntity($rawData)
    {
        return new Entity($rawData);
    }

    /**
     * WHERE params
     * @param string $value
     * @param string $key
     * @return $this
     */
    function setParamWhere($value, $key = null)
    {
        if (!$key) $key = uniqid();
        $this->paramsWhere[$key] = $value;
        return $this;
    }

    /**
     * JOIN params
     * @param string $value
     * @param string $key
     * @return $this
     */
    function setParamJoin($value, $key)
    {
        $this->paramsJoin[$key] = $value;
        return $this;
    }

    /**
     * Read data from cache if possible
     * If cache miss, write cache file
     * @param int $lifetime
     */
    function setCache($lifetime)
    {
        $this->queryCacheLifetime = $lifetime;
        return $this;
    }

    /**
     * Page number and pagesize
     * @param string $pageNo
     * @param string $pageSize
     * @return $this
     */
    function setPage($pageNo, $pageSize = null)
    {
        $pageSize = $pageSize ? $pageSize : $this->pageSize;
        // <Doanh>: lưu giá trị pageNo, pageSize
        $this->pageSize = $pageSize;
        $this->pageNo = $pageNo;
        $offset = ($pageNo - 1) * $pageSize;

        $this->limit($pageSize, $offset);
        return $this;
    }

    /**
     * Count total record
     * @param type $totalRecord
     * @return $this
     */
    function count(&$totalRecord)
    {
        $mapper = clone $this;
        $totalRecord = $mapper->select('COUNT(*)')->limit(1)->offset(0)->orderBy(null)->getOne();

        return $this;
    }

    /**
     * Comment hỗ trợ debug
     * @param type $cmt
     */
    function comment($cmt)
    {
        $this->comments[] = $cmt;
    }

    protected function cacheKey($dbMethod)
    {
        return md5($dbMethod . $this->__toString() . json_encode($this->getAllParams()));
    }

    /**
     * Get data from cached<Br>
     * Throw exception If cache not enabled/expire/miss
     * @param string $dbMethod GetOne|GetRow|GetCol|...
     * @return mixed
     * @throws Exception
     */
    function getCacheData($dbMethod)
    {
        if (!$this->queryCacheLifetime) {
            throw new Exception("Cache currently disabled");
        }
        $data = Cache::getInstance()->getCache($this->cacheKey($dbMethod));
        if (!$data) {
            throw new Exception("Cache missed");
        }

        return $data;
    }

    /**
     * Write cache data
     * @param type $dbMethod
     * @param type $data
     * @param type $lifetime
     * @return type
     */
    function writeCacheData($dbMethod, $data)
    {
        if (!$this->queryCacheLifetime) {
            return false;
        }
        return Cache::getInstance()->setCache($this->cacheKey($dbMethod), $data, $this->queryCacheLifetime);
    }

    /**
     * where $field in(?,?,?)
     * @param $field
     * @param $values
     */
    function filterIn($field, $values)
    {
        if (!is_array($values)) $values = [$values];
        if (!count($values)) return $this;

        foreach ($values as $i => $val) $values[$i] = "'" . $this->filterSqlInjection($val) . "'";

        $stm = "$field IN(" . implode(', ', $values) . ')';
        $this->where($stm, __FUNCTION__);
        return $this;
    }

    function filterSqlInjection($data)
    {
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Get one entity from dataset
     * @param type $callback
     * @return Entity
     */
    function getEntity($callback = null)
    {
        $this->limit(1);
        try {
            $row = $this->getCacheData(__FUNCTION__);
        } catch (\Exception $ex) {
            $row = $this->db->GetRow($this->__toString(), $this->getAllParams());
            $this->writeCacheData(__FUNCTION__, $row);
        }
        $entity = $this->makeEntity($row);
        if (is_callable($callback)) {
            call_user_func($callback, $row, $entity);
        }
        return $entity;
    }

    /**
     * Get one entity from dataset or fail if not exists
     * @param \Exception $customEx throw NotFound hoặc custom exceptopn
     * @return Entity
     */
    function getEntityOrFail($customEx = null)
    {
        $entity = $this->getRow();
        if (empty($entity)) {
            if ($customEx) {
                throw $customEx;
            } else {
                throw new \Company\Exception\NotFoundException();
            }
        }

        $entity = $this->makeEntity($entity);
        return $entity;
    }

    /** @return \Collection */
    function getEntities($callback = null)
    {
        try {
            $row = $this->getCacheData(__FUNCTION__);
        } catch (\Exception $ex) {
            $rows = $this->db->GetAll($this->__toString(), $this->getAllParams());
            $this->writeCacheData(__FUNCTION__, $rows);
        }

        $rows = $rows ? $rows : array();
        $entities = array();

        foreach ($rows as $row) {
            $entity = $this->makeEntity($row);
            if (is_callable($callback)) {
                call_user_func($callback, $row, $entity);
            }
            $entities[] = $entity;
        }

        return new \Collection($entities);
    }

    /** @return \Collection */
    function getAll()
    {
        try {
            $result = $this->getCacheData(__FUNCTION__);
        } catch (\Exception $ex) {
            $result = $this->db->GetAll($this->__toString(), $this->getAllParams());
            $this->writeCacheData(__FUNCTION__, $result);
        }

        return new \Collection($result);
    }

    /**
     * Get cell value from first col, first row
     * @return string
     */
    function getOne()
    {
        $this->limit(1);
        try {
            $val = $this->getCacheData(__FUNCTION__);
        } catch (\Exception $ex) {
            $val = $this->db->GetOne($this->__toString(), $this->getAllParams());
            $this->writeCacheData(__FUNCTION__, $val);
        }
        return $val;
    }

    /**
     * Get first col in array
     * @return array
     */
    function getCol()
    {
        try {
            $result = $this->getCacheData(__FUNCTION__);
        } catch (\Exception $ex) {
            $result = $this->db->GetCol($this->__toString(), $this->getAllParams());
            $this->writeCacheData(__FUNCTION__, $result);
        }

        return is_array($result) ? $result : [];
    }

    function getRow()
    {
        try {
            $result = $this->getCacheData(__FUNCTION__);
        } catch (\Exception $ex) {
            $result = $this->db->GetRow($this->__toString(), $this->getAllParams());
            $this->writeCacheData(__FUNCTION__, $result);
        }

        return is_array($result) ? $result : [];
    }

    /**
     * Get array of key => value from first+second col
     * @return array
     */
    function getAssoc()
    {
        try {
            $result = $this->getCacheData(__FUNCTION__);
        } catch (Exception $ex) {
            $result = $this->db->GetAssoc($this->__toString(), $this->getAllParams());
            $this->writeCacheData(__FUNCTION__, $result);
        }

        return is_array($result) ? $result : [];
    }

    /**
     * Filter by table id
     * @param string|array $id
     * @return $this
     */
    function filterID($id)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        $where = $this->tableAlias() . '.' . $this->getPkField() . ' IN(?' . (count($id) > 1 ? str_repeat(',?', count($id) - 1) : '') . ')';
        $this->where($where, __FUNCTION__);
        foreach ($id as $k => $i) {
            $this->setParamWhere($i, __FUNCTION__ . '.' . $k);
        }

        return $this;
    }

    /**
     * Loại bỏ những ID này khỏi kết quả trả về
     * @param type $id
     */
    function filterNotID($id)
    {
        if (empty($id)) {
            return $this;
        }
        if (!is_array($id)) {
            $id = [$id];
        }

        $where = $this->tableAlias() . '.' . $this->getPkField() . ' NOT IN(?' . str_repeat(',?', count($id) - 1) . ')';
        $this->where($where, __FUNCTION__);
        foreach ($id as $k => $i) {
            $this->setParamWhere($i, __FUNCTION__ . '.' . $k);
        }

        return $this;
    }

    /**
     *
     * @param type $parentCol
     * @param type $pathCol
     * @param type $srcCol
     * @param type $fromNode
     */
    function rebuildPath($parentCol, $pathCol, $srcCol, $fromNode = 0)
    {
        $this->db->startTrans();

        $table = $this->tableName();

        $node = $this->db->GetRow("SELECT * FROM $table WHERE " . $this->getPkField() . "=?", array($fromNode));
        $parentNode = $this->db->GetRow("SELECT * FROM $table WHERE " . $this->getPkField() . "=?", array(arrData($node, $parentCol)));
        $childNodes = $this->db->GetAll("SELECT * FROM $table WHERE $parentCol=?", array($fromNode));

        if (!empty($node)) {
            $path = arrData($parentNode, $pathCol, '/');
            $this->db->update($table, array($pathCol => $path . $node[$srcCol] . '/'), $this->getPkField() . '=?', array($node[$this->getPkField()]));
        }

        foreach ($childNodes as $node) {
            $this->rebuildPath($parentCol, $pathCol, $srcCol, $node[$this->getPkField()]);
        }

        $this->db->completeTrans();
    }

    /**
     * Limit default=1 để tránh update toàn bộ bảng
     * @param array $updateData
     * @return int so ban ghi duoc update
     */
    function update($updateData)
    {
        //tránh việc update nhầm toàn bộ bảng
        if (empty($this->join) && empty($this->where)) {
            throw new \Exception("Update all rows is forbidden");
        }

        $id = $this->select($this->tableAlias() . '.' . $this->getPkField())->getCol();
        if (!count($id)) {
            return 0;
        }

        $where = $this->getPkField() . ' IN(?' . str_repeat(',?', count($id) - 1) . ')';
        return $this->db->update($this->tableName(), $updateData, $where, $id);
    }

    /**
     * Cập nhật thông tin trong trường JSON
     * @param string $id
     * @param string $jsonField tên trường JSON
     * @param array $updateData
     * @throws Exception phải filter theo điều kiện, không được update tất cả bảng
     */
    function updateJson($jsonField, $updateData)
    {
        if (empty($this->where) && empty($this->join)) {
            throw new \Exception("Updating entire table is forbidden");
        }


        $ids = $this->select($this->tableAlias() . '.' . $this->getPkField())->getCol();
        if (!count($ids)) {
            return 0;
        }

        foreach ($ids as $id) {
            $mapper = $this->filterID($id);
            $jsonData = json_decode($mapper->select($jsonField)->getOne(), true);

            if (!$jsonData) {
                $jsonData = [];
            }

            foreach ($updateData as $k => $v) {
                $jsonData[$k] = $v;
            }

            $mapper->update([$jsonField => json_encode($jsonData)]);
        }
    }

    /**
     * Limit default=1 để tránh delete toàn bộ bảng
     * @return int so ban ghi duoc update
     */
    function delete()
    {
        if (empty($this->join) && empty($this->where)) {
            throw new \Exception("Update all rows is forbidden");
        }
        $where = explode("WHERE", $this->__toString())[1];
        //mysql where not take alias
        $where = str_replace($this->tableAlias() . '.', $this->tableName() . '.', $where);
        return $this->db->delete($this->tableName(), $where, $this->getAllParams());
    }

    /**
     * Có thể insert 1 bản ghi hoặc 1 mảng các bản ghi
     * @param array $updateData
     * @return bool
     */
    function insert($updateData)
    {
        if (isset($updateData[0]) && is_array($updateData[0])) {
            return $this->db->insertMany($this->tableName(), $updateData) !== false;
        } else {
            return $this->db->insert($this->tableName(), $updateData) !== false;
        }

    }

    /**
     * Có thể replace 1 bản ghi hoặc 1 mảng các bản ghi
     * @param array $updateData
     * @return type
     */
    function replace($updateData)
    {
        if (isset($updateData[0]) && is_array($updateData[0])) {
            return $this->db->replaceMany($this->tableName(), $updateData);
        } else {
            return $this->db->replace($this->tableName(), $updateData);
        }

    }

    /**
     * trả về params của join và params của where
     */
    function getAllParams()
    {
        return array_merge($this->paramsJoin, $this->paramsWhere);
    }

    /**
     * Copy this instance
     * @return type
     */
    function copy()
    {
        return clone $this;
    }

    function union()
    {
        $pkField = $this->getPkField();
        $arr = func_get_args();
        $ret = [];
        foreach ($arr as $rs) {
            foreach ($rs as $row) {
                $ret[$row->{$pkField}] = $row;
            }
        }

        return array_values($ret);
    }

//    /**
//     * id = 0 insert; id <> 0 update
//     * @param type $id
//     * @param type $updateData
//     * @return int id
//     */
//    function replace($updateData) {
//        if ($this->isExists()) {
//            $this->update($updateData);
//        } else {
//            $this->insert($updateData);
//        }
//    }

    /**
     * Check bản ghi tồn tại
     * @return bool true = tồn tại
     */
    function isExists()
    {
        if ($this->isConditionEmpty()) {
            throw new \BadMethodCallException("isExists must have where or join");
        }
        $result = $this->getRow();
        if (!$result) {
            return false;
        }
        foreach ($result as $field) {
            return true;
        }
        return false;
    }

    /**
     * Check bản ghi tồn tại, nếu không throw exceptop
     * @param \Exception $customEx Exception do NSD truyền
     * @throws \Company\Exception\NotFoundException
     */
    function existsOrFail($customEx = null)
    {
        if (!$this->isExists()) {
            if ($customEx) {
                throw $customEx;
            } else {
                throw new \Company\Exception\NotFoundException("existsOrFail");
            }
        }
    }

    /**
     * Nếu bản ghi tồn tại thì fail
     * @param \Exception $customEx Exception do NSD truyền
     * @throws \Company\Exception\NotFoundException
     */
    function existsThenFail($customEx = null)
    {
        if ($this->isExists()) {
            if ($customEx) {
                throw $customEx;
            } else {
                throw new \Company\Exception\NotFoundException("existsIsFail");
            }
        }
    }

    /**
     * Kiểm tra $value của $field có độc nhất không
     * @param type $field
     * @param type $value
     * @param mixed $id ID của bảng ghi trong trường hợp update
     */
    function isUnique($field, $value, $id = null)
    {
        $row = $this->makeInstance()->where("`$field`=?", __FUNCTION__)->setParamWhere($value, __FUNCTION__)->getEntity();
        $oldID = $row->{$this->getPkField()};
        if (!$oldID) {
            return true;
        }
        if ($id == $oldID) {
            return true;
        }
        return false;
    }

    /**
     * Kiểm tra $value của $field có độc nhất không, false = throw Exception
     * @param type $field
     * @param type $value
     * @param type $id
     * @param \Exception $customEx
     * @return boolean
     */
    function uniqueOrFail($field, $value, $id = null, $customEx = null)
    {
        if ($this->isUnique($field, $value, $id)) {
            return true;
        }

        if ($customEx) {
            throw $customEx;
        } else {
            throw new \Exception("Field value not unique");
        }
    }

    function startTrans()
    {
        $this->db->autoConnect();
        $this->db->StartTrans();
        $this->db->raiseErrorFn = array($this, 'transactionErrorHandler');
    }

    function failTrans()
    {
        $this->db->FailTrans();
    }

    function transactionErrorHandler($dbType, $execute, $errNo, $errMsg, $sql)
    {
        $err = ['errorNumber' => $errNo, 'errorMessage' => $errMsg];
        if ($this->db->debug) {
            $err += ['sql' => $sql, 'dbType' => $dbType, 'execute' => $execute];
        }
        $this->transactionErrors[] = $err;
    }

    /**
     *
     * @return bool true or false on rollback
     */
    function completeTrans()
    {
        $result = empty($this->transactionErrors);
        $this->db->CompleteTrans();
        $this->transactionErrors = [];
        $this->db->raiseErrorFn = null;
        return $result;
    }

    /**
     *
     * @return bool true or throw Exception on rollback
     */
    function completeTransOrFail($customEx = null)
    {
        $errors = $this->transactionErrors;
        $result = $this->completeTrans();
        if ($result) {
            return true;
        }

        if ($customEx) {
            throw $customEx;
        } else {
            throw new \Exception("Transaction error: " . json_encode($errors));
        }
    }
    public function rollbackTrans(){
        $this->db->RollbackTrans();
    }

    /**
     * Kiểm tra điều kiện rỗng hay không
     */
    function isConditionEmpty()
    {
        return empty($this->where) && empty($this->join);
    }

    function getPage()
    {
        $rows = $this->getEntities();
        $this->count($totalRecord);
        $pageCount = ceil($totalRecord / $this->pageSize);
        return array('rows' => $rows->toArray(), 'pageNo' => $this->pageNo, 'pageSize' => $this->pageSize, 'recordCount' => $totalRecord, 'pageCount' => $pageCount);
    }

    function convertDatetimeFormat($input, $outputFormat = DATE_ISO8601)
    {

        if ($input == "0000-00-00 00:00:00") return null;

        $timestamp = strtotime($input);
        if ($timestamp) return date($outputFormat, $timestamp);

        return $input;
    }

    function sqlDatetimeFormat(&$data, $outputFormat = DATE_RFC3339_EXTENDED_2)
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) $data[$k] = array_map(function ($item) use ($outputFormat) {
                return $this->convertDatetimeFormat($item, $outputFormat);
            }, $v); else
                $data[$k] = $this->convertDatetimeFormat($v, $outputFormat);
        }
    }

    /**
     * Kiểm tra $value của 1 bộ $field có độc nhất không
     * @param type $fields
     * @param mixed $id ID của bảng ghi trong trường hợp update
     */
    function isUniques($fields, $id = null)
    {
        $mapper = $this->makeInstance();
        foreach ($fields as $field => $value) {
            $mapper = $mapper->copy()->where("`$field`=?", __FUNCTION__)->setParamWhere($value, __FUNCTION__);
        }
        $row = $mapper->getEntity();

        $oldID = $row->{$this->getPkField()};
        if (!$oldID) {
            return true;
        }
        if ($id == $oldID) {
            return true;
        }
        return false;
    }

    /**
     * Kiểm tra $value của 1 bộ $field có độc nhất không, false = throw Exception
     * @param type $field
     * @param type $value
     * @param type $id
     * @param \Exception $customEx
     * @return boolean
     */
    function uniquesOrFail($fields, $id = null, $customEx = null)
    {
        if ($this->isUniques($fields, $id)) {
            return true;
        }

        if ($customEx) {
            throw $customEx;
        } else {
            throw new \Exception("Field value not unique");
        }
    }

    function uniqID($seq, $comment = '') {
        $this->db->StartTrans();
        $id = $this->db->GetOne("SELECT id FROM uniq_id WHERE seq=? LIMIT 1", [$seq]);
        // update seq
        if ($id === FALSE) {
            $createTableSQL = "CREATE TABLE uniq_id(`seq` VARCHAR(50) PRIMARY KEY NOT NULL, id BIGINT DEFAULT 1, `comment` TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
            //Tao bang
            $this->db->Execute($createTableSQL);
        }

        if (!$id || !strlen($id)) {
            $id = 1;
            $sql = "INSERT INTO uniq_id(`seq`,`id`,`comment`) VALUES(?,?,?)";
            $this->db->Execute($sql, [$seq, $id, $comment]);
        } else {
            // lock ban ghi
            $this->db->RowLock("uniq_id", "seq='$seq'");
            // id++
            $id++;
            // update
            $this->db->update('uniq_id', ['id' => $id], 'seq=?', [$seq]);
        }

        $rs = $this->db->CompleteTrans();

        if (!$rs) {
            throw new \Exception("Unique id fail:" . $this->db->ErrorMsg());
        }

        return $id;
    }
}
