function askPlay(){
	if(confirm('Vous voulez jouez ?')){
		window.location = "/play/play";
	}
	else{
		window.location = "/play";
	}
}
askPlay();