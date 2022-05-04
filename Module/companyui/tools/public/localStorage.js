App.localStorage = {}

App.localStorage.set = function (key, data) {
    localStorage.setItem(key, JSON.stringify(data));
}

App.localStorage.get = function (key, defaultVal) {
    if (typeof localStorage[key] == 'undefined')
        return defaultVal;
    var val = defaultVal;
    try {
        val = $.parseJSON(localStorage[key]);
    } catch (ex) {
        console.error('get storage failed', ex)
    }
    return val
}

App.localStorage.clear = function () {
    localStorage.clear();
}

App.localStorage.delete = function (key) {
    if (typeof localStorage[key] != 'undefined')
        delete localStorage[key];
}

App.localStorage.exists = function (key) {
    return typeof localStorage[key] != 'undefined' ? true : false;
}