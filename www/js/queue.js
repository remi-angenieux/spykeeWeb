var constantes = {
   'INTERVAL_REFRESH_QUEUE': 10000 // Interval de temps pour le rafrachissement de la file
};
function refreshQueue(refreshPeriod) {
    setTimeout("location.reload(true);",refreshPeriod);
}

window.setInterval(function() {refreshQueue()}, constantes.INTERVAL_REFRESH_QUEUE)
