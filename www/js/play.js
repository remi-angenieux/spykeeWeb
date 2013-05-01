alert('test');
// Constantes
var constantes = {
   'INTERVAL_REFRESH_IMG': 60 // Interval de temps pour le rafrachissement des images composant le flux vidéo
};
/*
 * Gestion du stream vidéo
 */
// Rafraichie l'image qui compose le flux vidéo
function refreshImgStream(){
	// TODO Verifier qu'il faut pas passer en absolue
	$("#imgStream").attr('src', '/videoStream/video.jpeg#'+ new Date().getTime());
}

window.setInterval(function(){refreshImgStream();}, constantes.INTERVAL_REFRESH_IMG);
$("#holdingLeft").css('background-color', 'red');
/*
 * Gestion des actions du robot
 */
function sendAction(action, callback){
	$.post('/?controller=play&action=ajax', { action: action }, function(data) {
			var result = jQuery.parseJSON(data);
			var text = 'Etat : '+ result.state + '<br />';
			text += 'Données : ' + result.data + '<br />';
			text += 'Description : ' + result.description + '<br />';
			text += 'Id de description : ' + result.idDescription + '<br />';
			$('.result').html(text);
			if(typeof callback === 'function' && callback())
				callback(result);
		});
}

function up(){
	sendAction('up');
}

function left(){
	sendAction('left');
}

function right(){
	sendAction('right');
}

function down(){
	sendAction('down');
}

function holdingUp(){
	sendAction('holdingUp', function(result){
		// Si l'action à bien été envoyée
		if(typeof result != 'undefined' && result.state == 1){
			$('#holdingUp').css('background-color', 'red');
			$('#holdingDown').css('background-color', 'green');
		}
		else{
			// TODO implémenté un gestionnaire d'erreur
			alert('Erreur');
		}
	});
}

function holdingDown(){
	sendAction('holdingDown', function(result){
		if(typeof result != 'undefined' && result.state == 1){
			$('#holdingDown').css('background-color', 'red');
			$('#holdingUp').css('background-color', 'green');
		}
	});
	/*if(sendAction('holdingDown').state == 1){
		$('#holdingDown').css('background-color', 'red');
		$('#holdingUp').css('background-color', 'green');
	}*/
}

function holdingLeft(){
	sendAction('holdingLeft', function(result){
		if(typeof result != 'undefined' && result.state == 1){
			$('#holdingLeft').css('background-color', 'red');
			$('#holdingRight').css('background-color', 'green');
		}
	});
}

function holdingRight(){
	sendAction('holdingRight', function(result){
		if(typeof result != 'undefined' && result.state == 1){
			$('#holdingRight').css('background-color', 'red');
			$('#holdingLeft').css('background-color', 'green');
		}
	});
}

function move(){
	sendAction('move');
}

function stop(){
	sendAction('stop');
}

function enableVideo(){
	sendAction('enableVideo');
}