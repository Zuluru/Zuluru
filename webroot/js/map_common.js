var map;
var baseIcon;

function initialize()
{
	resizeMap();
	map = new GMap2(document.getElementById("map"));
	map.setUIToDefault();

	baseIcon = new GIcon();
	baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);
}

// Swiped this from the Google Maps page
function resizeMap()
{
	var offset = 0;
	for (var elem = document.getElementById("map"); elem != null; elem = elem.offsetParent)
	{
		offset += elem.offsetTop;
	}
	var windowHeight = getWindowHeight();

	var height = windowHeight - offset - 10;
	if (height >= 0)
	{
		document.getElementById("map").style.height = height + "px";
	}
}

// Swiped this from the Google Maps page
function getWindowHeight()
{
	if (window.self && self.innerHeight)
	{
		return self.innerHeight;
	}
	if (document.documentElement && document.documentElement.clientHeight)
	{
		return document.documentElement.clientHeight;
	}
	return 0;
}
