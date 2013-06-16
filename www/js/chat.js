var url="/play/chatAjax";
var lastId=0;
window.setInterval(function(){getMessages();}, 500);

$(function(){
	
	$('#chatForm form').submit(function(){
		//showLoader('#loader');
		var message=$('#chatForm form input').val();
		$.post(url,{action:"addMessages",message:message},function(data){
			
		},"json");
		return false;
	})


	});


	function getMessages(){
		$.post(url,{action:"getMessages",lastId:lastId},function(data){
			$( "#chat" ).append(data.text);
			lastId=(data.lastId);
		},"json");
		
		return false;
	}
	




/*function showLoader(div){
	$(div).append('<div id="floatingCirclesG"><div class="f_circleG" id="frotateG_01"></div><div class="f_circleG" id="frotateG_02"></div><div class="f_circleG" id="frotateG_03"></div><div class="f_circleG" id="frotateG_04"></div><div class="f_circleG" id="frotateG_05"></div><div class="f_circleG" id="frotateG_06"></div><div class="f_circleG" id="frotateG_07"></div><div class="f_circleG" id="frotateG_08"></div></div>');
			
			
		
}*/