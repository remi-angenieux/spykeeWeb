// Constantes
var constants = {
   'INTERVAL_REFRESH_IMG': 60, // Interval de temps pour le rafrachissement des images composant le flux vidéo
   'DEFAULT_SPEED': 100,
   'KEY_DOWN': 40,
   'KEY_UP': 38,
   'KEY_LEFT': 37,
   'KEY_RIGHT': 39,
   'KEY_CTR': 17
};
/*
 * Gestion du stream vidéo
 */
// Rafraichie l'image qui compose le flux vidéo
/*function refreshImgStream(){
	$("#imgStream").attr('src', '/videoStream/video.jpeg#'+ new Date().getTime());
}*/

//window.setInterval(function(){refreshImgStream();}, constants.INTERVAL_REFRESH_IMG);
/*
 * Gestion des actions du robot
 */
var holding = {
	'up': false,
	'down': false,
	'left': false,
	'right': false
};

// Pour envoyer des paquets au controller
function sendAction(action, callback, data){
	var post;
	if (data == null)
		post = { action: action };
	else
		post = { action: action, data: data };
	$.post('/play/ajax', post, function(data) {
		var robotConsole = $('#console');
		//$('p', robotConsole).html(data);
		var result = jQuery.parseJSON(data);
		var currentTime = new Date();
		var consoleText=$('p', robotConsole).html();
		consoleText += '['+currentTime.getHours()+'h'+currentTime.getMinutes()+'min'+currentTime.getSeconds()+'] ';
		if (result.state != 1)
			consoleText += '<span class="consoleError">ERREUR :</span> ';
		consoleText += result.description;
		if (result.data != null && result.data != '')
			consoleText += ' ('+result.data+')';
		consoleText += '<br />';
		$('p', robotConsole).html(consoleText);
		// On scroll si il y a besoin pour voir le message
		robotConsole.scrollTop(robotConsole[0].scrollHeight);
		if(typeof callback === 'function')
			callback(result);
	});
}

$(document).ready(function() {
	// Gestion de l'affichage de la console
	var RobotConsole = $('#console');
	RobotConsole.slideUp('slow');
	$('#showHideConsole').click(function(e){
		e.preventDefault();
		RobotConsole.slideToggle('slow', function(){
			RobotConsole.scrollTop(RobotConsole[0].scrollHeight);
		});
	});
	// Gestion de la vitesse avec un slider
	// Définition du slider
	var speedSlider = function(speed) {
		$('#speed').slider({max: 128,
							min: 28,
							step: 1,
							value: speed,
							slide: setSpeed,
							stop: setSpeed
		});
	};
	// Recupère la vitesse en cours du robot
	function getSpeed(){
		sendAction('getSpeed', function(result){
			if(typeof result != 'undefined' && result.state == 1)
				speedSlider(result.data);
			else
				speedSlider(constants.DEFAULT_SPEED);
		});
	}
	// Afficher le slider
	getSpeed();
});

/*
 * Actions utilisateur
 */
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
		if(typeof result != 'undefined' && result.state == 1 && holding.up == false){
			holding.up = true;
			holding.down = false;
			$('#holdingUp').removeClass('up');
			$('#holdingUp').addClass('down');
			$('#holdingDown').removeClass('down');
			$('#holdingDown').addClass('up');
		} // Si le bouton avancé est déjà "enfoncé"
		else if(typeof result != 'undefined' && result.state == 1 && holding.up == true){
			holding.up = false;
			holding.down = false;
			$('#holdingUp').removeClass('down');
			$('#holdingUp').addClass('up');
			$('#holdingDown').removeClass('down');
			$('#holdingDown').addClass('up');
		}
	});
}

function holdingDown(){
	sendAction('holdingDown', function(result){
		if(typeof result != 'undefined' && result.state == 1 && holding.down == false){
			holding.down = true;
			holding.up = false;
			$('#holdingDown').removeClass('up');
			$('#holdingDown').addClass('down');
			$('#holdingUp').removeClass('down');
			$('#holdingUp').addClass('up');
		}
		else if(typeof result != 'undefined' && result.state == 1 && holding.down == true){
			holding.down = false;
			holding.up = false;
			$('#holdingDown').removeClass('down');
			$('#holdingDown').addClass('up');
			$('#holdingUp').removeClass('down');
			$('#holdingUp').addClass('up');
		}
	});
}

function holdingLeft(){
	sendAction('holdingLeft', function(result){
		if(typeof result != 'undefined' && result.state == 1 && holding.left == false){
			holding.left = true;
			holding.right = false;
			$('#holdingLeft').removeClass('up');
			$('#holdingLeft').addClass('down');
			$('#holdingRight').removeClass('down');
			$('#holdingRight').addClass('up');
		}
		else if(typeof result != 'undefined' && result.state == 1 && holding.left == true){
			holding.left = false;
			holding.right = false;
			$('#holdingLeft').removeClass('down');
			$('#holdingLeft').addClass('up');
			$('#holdingRight').removeClass('down');
			$('#holdingRight').addClass('up');
		}
	});
}

function holdingRight(){
	sendAction('holdingRight', function(result){
		if(typeof result != 'undefined' && result.state == 1 && holding.right == false){
			holding.right = true;
			holding.left = false;
			$('#holdingRight').removeClass('up');
			$('#holdingRight').addClass('down');
			$('#holdingLeft').removeClass('down');
			$('#holdingLeft').addClass('up');
		}
		else if(typeof result != 'undefined' && result.state == 1 && holding.right == true){
			holding.right = false;
			holding.left = false;
			$('#holdingRight').removeClass('down');
			$('#holdingRight').addClass('up');
			$('#holdingLeft').removeClass('down');
			$('#holdingLeft').addClass('up');
		}
	});
}

function move(){
	sendAction('move');
}

function stop(){
	sendAction('stop', function(result){
		if(typeof result != 'undefined' && result.state == 1 && holding.right == false){
			$('#holdingUp').removeClass('down');
			$('#holdingUp').addClass('up');
			$('#holdingDown').removeClass('down');
			$('#holdingDown').addClass('up');
			$('#holdingLeft').removeClass('down');
			$('#holdingLeft').addClass('up');
			$('#holdingRight').removeClass('down');
			$('#holdingRight').addClass('up');
			holding.up=false;
			holding.down=false;
			holding.left=false;
			holding.right=false;
		}
	});
}

function enableVideo(){
	sendAction('enableVideo');
}

function setSpeed(){
	sendAction('setSpeed', null, $('#speed').slider('value'));
}

// Assigne les touches fléchées aux actions du robot
$(document).keydown(function(e){
	switch(e.keyCode){
		case constants.KEY_LEFT: 
	    	holdingLeft();
	    	return false;
	    	break;
		case constants.KEY_UP:
			holdingUp();
			return false;
			break;
		case constants.KEY_RIGHT:
			holdingRight();
			return false;
			break;
		case constants.KEY_DOWN:
			holdingDown();
			return false;
			break;
		case constants.KEY_CTR:
			stop();
			return false;
			break;
	}
});
