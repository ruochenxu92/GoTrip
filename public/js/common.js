function getParamValue(f_opt) {
    var q = document.URL.split(f_opt);
    if (q.length > 1) {
        var p = q[1].split('&');
        var p = p[0].split('=');
        return p[1];
    }
    return -1;
}
