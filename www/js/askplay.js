function askPlay(){
	if(confirm('Vous voulez jouez ?')){
		window.location = "http://spykee.lan/play/play";
	}
	else{
		window.location = "http://spykee.lan/play";
	}
}
askPlay();