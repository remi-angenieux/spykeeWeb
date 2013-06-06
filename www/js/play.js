// Constantes
var constantes = {
   'INTERVAL_REFRESH_IMG': 60 // Interval de temps pour le rafrachissement des images composant le flux vidéo
   'INTERVAL_REFRESH_QUEUE': 5000 // Interval de temps pour le rafrachissement de l'invitation à jouer
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
/*
 * Gestion de la vitesse du robot
 */
/*function repositionTooltip( e, ui ){
    var div = $(ui.handle).data("tooltip").$tip[0];
    var pos = $.extend({}, $(ui.handle).offset(), { width: $(ui.handle).get(0).offsetWidth,
                                                    height: $(ui.handle).get(0).offsetHeight
              });
    
    var actualWidth = div.offsetWidth;
    
    tp = {left: pos.left + pos.width / 2 - actualWidth / 2};
    $(div).offset(tp);
    
    $(div).find(".tooltip-inner").text( ui.value );        
}*/

$(function() {
	$('#speed').slider({max: 128,
						min: 1,
						step: 1,
						value: 100,
						slide: setSpeed/*,
						slide: repositionTooltip,
						stop: repositionTooltip*/
	});
	//$("#speed .ui-slider-handle:first").tooltip( {title: $("#speed").slider("value"), trigger: "manual"}).tooltip('option', "show");
});
/*
 * Gestion des actions du robot
 */
var holding = {
	'up': false,
	'down': false,
	'left': false,
	'right': false
};


function sendAction(action, callback, data){
	var post;
	if (data == null)
		post = { action: action };
	else
		post = { action: action, data: data };
	$.post('/play/ajax', post, function(data) {
		var result = jQuery.parseJSON(data);
		var text = 'Etat : '+ result.state + '<br />';
		text += 'Données : ' + result.data + '<br />';
		text += 'Description : ' + result.description + '<br />';
		text += 'Id de description : ' + result.idDescription + '<br />';
		$('.result').html(text);
		if(typeof callback === 'function')
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
		if(typeof result != 'undefined' && result.state == 1 && holding.up == false){
			holding.up = true;
			$('#holdingUp').css('background-color', 'red');
			$('#holdingDown').css('background-color', 'green');
		} // Si le bouton avancé est déjà "enfoncé"
		else if(typeof result != 'undefined' && result.state == 1 && holding.up == true){
			holding.up = false;
			$('#holdingUp').css('background-color', 'green');
		}
		else{
			// TODO implémenté un gestionnaire d'erreur
			alert('Erreur');
		}
	});
}

function holdingDown(){
	sendAction('holdingDown', function(result){
		if(typeof result != 'undefined' && result.state == 1 && holding.down == false){
			holding.down = true;
			$('#holdingDown').css('background-color', 'red');
			$('#holdingUp').css('background-color', 'green');
		}
		else if(typeof result != 'undefined' && result.state == 1 && holding.down == true){
			holding.down = false;
			$('#holdingDown').css('background-color', 'green');
		}
	});
}

function holdingLeft(){
	sendAction('holdingLeft', function(result){
		if(typeof result != 'undefined' && result.state == 1 && holding.left == false){
			holding.left = true;
			$('#holdingLeft').css('background-color', 'red');
			$('#holdingRight').css('background-color', 'green');
		}
		else if(typeof result != 'undefined' && result.state == 1 && holding.left == true){
			holding.left = false;
			$('#holdingLeft').css('background-color', 'green');
		}
	});
}

function holdingRight(){
	sendAction('holdingRight', function(result){
		if(typeof result != 'undefined' && result.state == 1 && holding.right == false){
			holding.right = true;
			$('#holdingRight').css('background-color', 'red');
			$('#holdingLeft').css('background-color', 'green');
		}
		else if(typeof result != 'undefined' && result.state == 1 && holding.right == true){
			holding.right = false;
			$('#holdingRight').css('background-color', 'green');
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

function setSpeed(){
	sendAction('setSpeed', null, $('#speed').slider('value'));
}
