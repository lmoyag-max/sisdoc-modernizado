function setContentSize() {
	if(!document.getElementById) return;

	getWindowSize();

	var content = document.getElementById("content");
	var nav = document.getElementById("nav");

	var numW = (navigator.userAgent.indexOf("MSIE") == -1) ? 40 : 20;
	var numH = (navigator.userAgent.indexOf("MSIE") == -1) ? 60 : 40;

	content.style.height = winHeight - document.getElementById("heading").offsetHeight - numH + 'px';
	content.style.width = winWidth - nav.offsetWidth - numW + 'px';
	content.style.overflow = "auto";

	// 130 is the rough height of the top image, plus padding
	nav.style.height = winHeight - 130 + 'px';
	nav.style.overflow = "auto";
}

// display left navigation sub menu's
var dleia = null;

function displaySub(link, name) {
	if(!document.getElementById) return;

	leia = document.getElementById(name);
	current = (leia.style.display == 'block') ? 'none' : 'block';
	leia.style.display = current;
	link.title = (leia.style.display == 'block') ? 'Click to collapse' : 'Click to expand';

	if(dleia != null) {
		dleia.style.display = 'none';
		var elm = dleia.parentNode.getElementsByTagName('span')[0];
		elm.title = 'Click to expand';
	}
	(dleia != leia) ? dleia = leia : dleia = null;
}