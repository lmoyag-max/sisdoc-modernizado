/* Dynamic Layer Code Library Version 1.8 written by Stoo -=stoo@darthstoo.com=- */
function DL_leiaObj(objName)
{
	this.ID=objName;
	this.show=(document.layers) ? 'show' : 'visible';
	this.hide=(document.layers) ? 'hide' : 'hidden';
	this.Leia=getObj(objName);
	this.clip=(document.layers) ? this.Leia.clip : this.Leia.style;
	this.style=(document.layers) ? this.Leia : this.Leia.style;
	this.top=(document.layers) ? this.Leia.top : this.Leia.offsetTop;
	this.left=(document.layers) ? this.Leia.left : this.Leia.offsetLeft;
	this.height=(document.layers) ? this.clip.height : this.Leia.offsetHeight;
	this.width=(document.layers) ? this.clip.width : this.Leia.offsetWidth;
	this.vis=this.style.visibility||this.show;
	this.zindex=this.style.zIndex||0;
	if(document.layers) 
	{
		this.tclip=this.clip.top, this.lclip=this.clip.left, this.bclip=this.clip.height, this.rclip=this.clip.width;
	}
	else if(this.style.clip)
	{
		var clipVals=getClip(this.style.clip);
		this.tclip=clipVals[0], this.lclip=clipVals[3], this.bclip=clipVals[2], this.rclip=clipVals[1];
	}
	else
	{
		this.tclip=0, this.lclip=0, this.bclip=this.height, this.rclip=this.width;
	}
	this.toggleVis=function() {
		this.vis=(this.style.visibility==this.hide) ? this.show : this.hide;
		this.style.visibility=this.vis;
	}
	this.hideLeia=function() {
		this.vis=this.hide;
		this.style.visibility=this.vis;
	}
	this.showLeia=function() {
		this.vis=this.show;
		this.style.visibility=this.vis;
	}	
	this.moveTo=function(x, y) {
		this.top=x=(x=='auto') ? this.top : x;
		this.left=y=(y=='auto') ? this.left : y;

		if(navigator.appName=='Netscape' && document.getElementById) { x=x+'px', y=y+'px'; }
		this.style.top=x, this.style.left=y;
	}
	this.moveBy=function(x, y) {
		x=(x=='auto') ? 0 : x;
		y=(y=='auto') ? 0 : y;
		this.moveTo(x+this.top, y+this.left);
	}
	this.writeText=function(txt) {
		if(document.layers)
		{
			this.Leia.document.write(txt);
			this.Leia.document.close();
		}
		else
		{
			this.Leia.innerHTML=txt;
		}
	}
	this.clipBy=function(t, r, b, l) {
		t=(t=='auto') ? 0 : t;
		r=(r=='auto') ? 0 : r;
		b=(b=='auto') ? 0 : b;
		l=(l=='auto') ? 0 : l;
		this.clipTo(this.tclip+t, this.rclip+r, this.bclip+b, this.lclip+l);
	}
	this.clipTo=function (t, r, b, l) {
		this.tclip=t=(t=='auto') ? this.tclip : t;
		this.rclip=r=(r=='auto') ? this.rclip : r;
		this.bclip=b=(b=='auto') ? this.bclip : b;
		this.lclip=l=(l=='auto') ? this.lclip : l;

		if(document.layers)
		{
			this.clip.top=t, this.clip.left=l, this.clip.bottom=b, this.clip.right=r;
		}
		else
		{
			this.style.clip='rect('+t+'px '+r+'px '+b+'px '+l+'px)';
		}
	}
	this.resizeBy=function(w, h) {
		w=(w=='auto') ? 0 : w;
		h=(h=='auto') ? 0 : h;
		this.resizeTo(this.width+w, this.height+h);
	}
	this.resizeTo=function(w, h) {
		this.width=w=(w=='auto') ? this.width : w;
		this.height=h=(h=='auto') ? this.height : h;

		if(navigator.appName=='Netscape' && document.getElementById) { w=w+'px', h=h+'px'; }
		(document.layers) ? this.Leia.resizeTo(w, h) : this.style.width=w, this.style.height=h;
	}
	this.setZindexBy=function(z) {
		this.setZindexTo(this.zindex+z);
	}
	this.setZindexTo=function(z) {
		this.zindex=z;
		this.style.zIndex=z;
	}
	this.setBackground=function(c) {
		(c.indexOf(".") != -1) ? this.setImage(c) : this.setColour(c);
	}
	this.removeBackground=function(c) {
		(c=='image') ? this.setImage(null) : this.setColour('transparent');
	}
	this.setColour=function(c) {
		(document.layers) ? this.Leia.bgColor=(c=='transparent') ? null : c : this.style.backgroundColor=c;
	}
	this.setImage=function(i) {
		(document.layers) ? this.Leia.background.src=i : this.style.backgroundImage=(i) ? 'url('+i+')' : 'none';
	}
	this.getBox=function() {
		var box=new Object();
		box["top"]=this.top;
		box["right"]=(document.layers) ? this.left+this.Leia.clip.width : this.left+this.Leia.offsetWidth;
		box["bottom"]=(document.layers) ? this.top+this.Leia.clip.height : this.top+this.Leia.offsetHeight;
		box["left"]=this.left;
		return box;
	}
	function getObj(objName)
	{
		if(document.getElementById) { return document.getElementById(objName); }
		else if(document.all) { return document.all[objName]; }
		else if(document.layers) { return document.layers[objName]; }
	}
	function getClip(target)
	{
		var clipVal=target;
		clipVal=clipVal.substring(5,clipVal.length-2);
		clipVal=clipVal.split(' ');
		for(var i=0; i<clipVal.length; i++)
		{
			clipVal[i]=parseInt(clipVal[i]);
		}
		return clipVal;
	}
}