function RoutingManager() {}

/**
 * @param {String} name
 * @param {RouteParams} parameters
 */
RoutingManager.generate = function (name, parameters) {
    var baseRoute = Routing.generate(name, parameters);

    if (window.location.port != "") {
        return this._addPortToUrl(baseRoute);
    }

    return baseRoute;
};

/**
 * @param {String} baseRoute
 * @returns {String}
 * @private
 */
RoutingManager._addPortToUrl = function (baseRoute) {
    var a = document.createElement('a');
    a.href = baseRoute;
    a.port = window.location.port;

    return a.href;
};