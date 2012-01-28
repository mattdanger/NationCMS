/* ------------------------------------
/*  Live URL Title Function
/* -------------------------------------*/

function liveUrlTitle() {

    var defaultTitle = '';
    var NewText = document.getElementById("title").value;
    
    if (defaultTitle != '') {
        if (NewText.substr(0, defaultTitle.length) == defaultTitle)
        {
            NewText = NewText.substr(defaultTitle.length)
        }	
    }
    
    NewText = NewText.toLowerCase();
    var separator = "_";

    if (separator != "_") {

        NewText = NewText.replace(/\_/g, separator);

    } else {

        NewText = NewText.replace(/\-/g, separator);

    }

    // Foreign Character Attempt
    
    var NewTextTemp = '';
    for(var pos=0; pos<NewText.length; pos++) {
        var c = NewText.charCodeAt(pos);
        
        if (c >= 32 && c < 128) {
            NewTextTemp += NewText.charAt(pos);
        } else {
            if (c == '223') {NewTextTemp += 'ss'; continue;}
            if (c == '224') {NewTextTemp += 'a'; continue;}
            if (c == '225') {NewTextTemp += 'a'; continue;}
            if (c == '226') {NewTextTemp += 'a'; continue;}
            if (c == '229') {NewTextTemp += 'a'; continue;}
            if (c == '227') {NewTextTemp += 'ae'; continue;}
            if (c == '230') {NewTextTemp += 'ae'; continue;}
            if (c == '228') {NewTextTemp += 'ae'; continue;}
            if (c == '231') {NewTextTemp += 'c'; continue;}
            if (c == '232') {NewTextTemp += 'e'; continue;}
            if (c == '233') {NewTextTemp += 'e'; continue;}
            if (c == '234') {NewTextTemp += 'e'; continue;}
            if (c == '235') {NewTextTemp += 'e'; continue;}
            if (c == '236') {NewTextTemp += 'i'; continue;}
            if (c == '237') {NewTextTemp += 'i'; continue;}
            if (c == '238') {NewTextTemp += 'i'; continue;}
            if (c == '239') {NewTextTemp += 'i'; continue;}
            if (c == '241') {NewTextTemp += 'n'; continue;}
            if (c == '242') {NewTextTemp += 'o'; continue;}
            if (c == '243') {NewTextTemp += 'o'; continue;}
            if (c == '244') {NewTextTemp += 'o'; continue;}
            if (c == '245') {NewTextTemp += 'o'; continue;}
            if (c == '246') {NewTextTemp += 'oe'; continue;}
            if (c == '249') {NewTextTemp += 'u'; continue;}
            if (c == '250') {NewTextTemp += 'u'; continue;}
            if (c == '251') {NewTextTemp += 'u'; continue;}
            if (c == '252') {NewTextTemp += 'ue'; continue;}
            if (c == '255') {NewTextTemp += 'y'; continue;}
            if (c == '257') {NewTextTemp += 'aa'; continue;}
            if (c == '269') {NewTextTemp += 'ch'; continue;}
            if (c == '275') {NewTextTemp += 'ee'; continue;}
            if (c == '291') {NewTextTemp += 'gj'; continue;}
            if (c == '299') {NewTextTemp += 'ii'; continue;}
            if (c == '311') {NewTextTemp += 'kj'; continue;}
            if (c == '316') {NewTextTemp += 'lj'; continue;}
            if (c == '326') {NewTextTemp += 'nj'; continue;}
            if (c == '353') {NewTextTemp += 'sh'; continue;}
            if (c == '363') {NewTextTemp += 'uu'; continue;}
            if (c == '382') {NewTextTemp += 'zh'; continue;}
            if (c == '256') {NewTextTemp += 'aa'; continue;}
            if (c == '268') {NewTextTemp += 'ch'; continue;}
            if (c == '274') {NewTextTemp += 'ee'; continue;}
            if (c == '290') {NewTextTemp += 'gj'; continue;}
            if (c == '298') {NewTextTemp += 'ii'; continue;}
            if (c == '310') {NewTextTemp += 'kj'; continue;}
            if (c == '315') {NewTextTemp += 'lj'; continue;}
            if (c == '325') {NewTextTemp += 'nj'; continue;}
            if (c == '352') {NewTextTemp += 'sh'; continue;}
            if (c == '362') {NewTextTemp += 'uu'; continue;}
            if (c == '381') {NewTextTemp += 'zh'; continue;}        
        }
    }

    NewText = NewTextTemp;
    
    NewText = NewText.replace('/<(.*?)>/g', '');
    NewText = NewText.replace('/\&#\d+\;/g', '');
    NewText = NewText.replace('/\&\#\d+?\;/g', '');
    NewText = NewText.replace('/\&\S+?\;/g','');
    NewText = NewText.replace(/['\"\?\.\!*$\#@%;:,=\(\)\[\]]/g,'');
    NewText = NewText.replace(/\s+/g, separator);
    NewText = NewText.replace(/\//g, separator);
    NewText = NewText.replace(/[^a-z0-9-_]/g,'');
    NewText = NewText.replace(/\+/g, separator);
    NewText = NewText.replace(/[-_]+/g, separator);
    NewText = NewText.replace(/\&/g,'');
    NewText = NewText.replace(/-$/g,'');
    NewText = NewText.replace(/_$/g,'');
    NewText = NewText.replace(/^_/g,'');
    NewText = NewText.replace(/^-/g,'');
    
    if (document.getElementById("url_title")) {

        document.getElementById("url_title").value = "" + NewText;			
    } else {

        document.forms['entryform'].elements['url_title'].value = "" + NewText; 

    }		
}
