function requestNewPhoto(photo) {
    var http_request = false;
    var url = '/request_photo_info.php?photo='+photo;
    if (window.XMLHttpRequest) { // Mozilla, Safari,...
        http_request = new XMLHttpRequest();
        if (http_request.overrideMimeType) {
            http_request.overrideMimeType('text/xml');
        }
    } else if (window.ActiveXObject) { // IE
        try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }

    if (!http_request) {
        alert('Cannot create an XMLHTTP instance');
        return false;
    }

    http_request.onreadystatechange = function() { alertContents(http_request); };
    http_request.open('GET', url, true);
    http_request.send(null);

}

function alertContents(http_request) {
    var photo_dir = '/files/photo/';
        if (http_request.readyState == 0) {
        document.getElementById('mainImageIMG').innerHTML = "<br /> &nbsp; <b>Sending Request...</b>";
    }
    if (http_request.readyState == 1) {
        document.getElementById('mainImageIMG').innerHTML = "<br /> &nbsp; <b>Loading...</b>";
    }
    if (http_request.readyState == 2) {
        document.getElementById('mainImageIMG').innerHTML = "<br /> &nbsp; <b>Loaded.</b>";
    }
    if (http_request.readyState == 3) {
        document.getElementById('mainImageIMG').innerHTML = "<br /> &nbsp; <b>Ready.</b>";
    }
    if (http_request.readyState == 4) {
        if (http_request.status == 200) {
            document.getElementById('mainImageIMG').innerHTML = "";
            var response = http_request.responseXML;
            var filename = response.getElementsByTagName('filename')[0].firstChild.data;
            var width = response.getElementsByTagName('width')[0].firstChild.data;
            var height = response.getElementsByTagName('height')[0].firstChild.data;
            var photographer = response.getElementsByTagName('photographer')[0].firstChild.data;
            var date = response.getElementsByTagName('date')[0].firstChild.data;
            var location = response.getElementsByTagName('location')[0].firstChild.data;
            var description = response.getElementsByTagName('description')[0].firstChild.data;
            var owner = ' ';
            swapImage(photo_dir+'display/'+filename, width, height, photo_dir+'full_size/'+filename, owner, photographer, description, date, location);
        } else {
            document.getElementById('mainImageIMG').innerHTML = "<br /> &nbsp; <b>There was a problem with the request.</b>";
        }
    }
} 


function swapImage(s, width, height, l, owner, photographer, des, date, loc) {
    if (document.getElementById("imageOwner").innerHTML != " ") {
    	document.getElementById("imageOwner").innerHTML=owner;
    }
	document.getElementById("mainImageIMG").style.backgroundImage="url("+s+")";
	document.getElementById("mainImageIMG").style.width=" "+width+"px";
	document.getElementById("mainImageIMG").style.height=" "+height+"px";
	document.getElementById("supersize_photo").href=s.replace("display","full_size");
	document.getElementById("mainImageLink").onclick="window.open('high_res_photo_pop.php?p="+l+"','null','width=830,height=630,resizeable=yes,scrollbars=yes,toolbar=no,location=no,menubar=no,copyhistory=no,status=yes')";
	document.getElementById("mainImageLink").onclick="alert('hi')";
	document.getElementById("imagePhotographer").innerHTML="Photo: "+photographer+" &nbsp; ";

    if (des != "None ") {
        document.getElementById("imageDescription").innerHTML="<b>Description:</b> "+des;
    } else {
        document.getElementById("imageDescription").innerHTML=" ";
    
    }

	document.getElementById("imageDate").innerHTML="<b>Date:</b> "+date;
	document.getElementById("imageLocation").innerHTML="<b>Taken at:</b> "+loc;
}
