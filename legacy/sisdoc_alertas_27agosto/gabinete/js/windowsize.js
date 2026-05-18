var winWidth = 0, winHeight = 0;

function getWindowSize()
{
	winWidth = (window.innerWidth) ? window.innerWidth : document.body.clientWidth;
	winHeight = (window.innerHeight) ? window.innerHeight : document.body.clientHeight;
}