mkdir /tmp/local/
mkdir /tmp/share/
rsync -rtv /var/www/html/node_modules /tmp/local/
chmod +x /tmp/local/node_modules/.bin/*
