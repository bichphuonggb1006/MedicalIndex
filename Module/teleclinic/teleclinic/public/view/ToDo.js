
class ToDo extends Component {
    render() {
        return(
            <div>
                <div className="container">
                    <h1><center>TODOLIST</center></h1>
                    <br/>
                    <button className="btn btn-info">Thêm mới</button>
                    <table className="table table-striped">
                        <tr>
                            <th>ID</th>
                            <th>Task</th>
                            <th>Thao tác</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Hoàn thành bài tập</td>
                            <td>
                                <button className="btn btn-info">Sửa</button>
                                <button className="btn btn-danger">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Hoàn thành bài tập</td>
                            <td>
                                <button className="btn btn-info">Sửa</button>
                                <button className="btn btn-danger">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Hoàn thành bài tập</td>
                            <td>
                                <button className="btn btn-info">Sửa</button>
                                <button className="btn btn-danger">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Hoàn thành bài tập</td>
                            <td>
                                <button className="btn btn-info">Sửa</button>
                                <button className="btn btn-danger">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Hoàn thành bài tập</td>
                            <td>
                                <button className="btn btn-info">Sửa</button>
                                <button className="btn btn-danger">Xóa</button>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Hoàn thành bài tập</td>
                            <td>
                                <button className="btn btn-info">Sửa</button>
                                <button className="btn btn-danger">Xóa</button>
                            </td>
                        </tr>

                    </table>
                </div>
            </div>
        );
    }
}