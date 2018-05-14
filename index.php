<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Data Finder</title>

    <!-- Bootstrap -->
   <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bootstrap-treeview/public/js/bootstrap-treeview.js"></script>

<style>
.breadcrumb {
        display: inline;
        text-align: center;
        border-style: solid;
        border-width: 1px;
        border-radius: 6px;
        padding: 5px 7px 5px 7px;
        margin-right: 5px;
        background-color: rgb(200, 240, 250);
}

.x-btn {
        font-size: 17px;
        font-weight: bold;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        filter: alpha(opacity=20);
        opacity: .4;
        position: relative;
        top: 0px;
}

.x-btn:hover,
.x-btn:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
  filter: alpha(opacity=80);
  opacity: .8;
}

button.x-btn {
  -webkit-appearance: none;
  padding: 0;
  cursor: pointer;
  background: transparent;
  border: 0;
}

</style>


<script>
//Globals
//MUST match globals in results.php
var PUBLISHERS = "Datenlieferanten";
var REGIONAL_INSTITUTIONS = "Regionale Stellen";
var STATE_INSTITUTIONS = "Staatliche Stellen";
var PRIVATE_INSTITUTIONS = "Private Insititutionen";

var ISSUED = "Erstellungsdatum";
var THIS_WEEK = "Diese Woche";
var THIS_MONTH = "Dieser Monat";
var YEAR_2016 = "Jahr 2016";
var YEAR_2015 = "Jahr 2015";
var YEAR_2014 = "Jahr 2014";
var YEAR_2013 = "Jahr 2013";
var YEAR_2012 = "Jahr 2012";
var MODIFIED = "Letztes Updatedatum";
var FORMAT = "Format";
var GEO = "Geoinformation";
var PICTURE = "Bild";
var STRUCTURED ="Strukturiert";
var TEXT = "Text";
var OTHER = "Andere";

var THEME = "Thema";
var FINANCE = "Finanzen";
var PEOPLE = "Menschen";
var ENVIRONMENT = "Umwelt";
var EDUCATION = "Bildung";
var HEALTH = "Gesundheit";
var ECONOMY = "Wirtschaft";
var ART = "Kunst und Kultur";
var POLITICS = "Politik und Recht";
var GEOGRAPHY = "Geographie";

var REGION = "Region";

var iFrameWin;



function validateSearch(){

        //" not allowed in search term since its used for separation of multiple terms in form
        var searchbox = document.getElementById("-searchbox").value;
        var forbiddenChars = "";
        if(searchbox.indexOf('"') > -1)forbiddenChars = '"';
        if(searchbox.indexOf('+') > -1)forbiddenChars += '+';
        if(searchbox.indexOf('-') > -1)forbiddenChars += '-';
        if(searchbox.indexOf('=') > -1)forbiddenChars += '=';
        if(searchbox.indexOf('>') > -1)forbiddenChars += '>';
        if(searchbox.indexOf('<') > -1)forbiddenChars += '<';
        if(searchbox.indexOf(';') > -1)forbiddenChars += ';';
        if(searchbox.indexOf('\\') > -1)forbiddenChars += '\\';
        if(searchbox.indexOf('&') > -1)forbiddenChars += '&';
        if(searchbox.indexOf('%') > -1)forbiddenChars += '%';
        if(searchbox.indexOf('/') > -1)forbiddenChars += '/';


        if(forbiddenChars != ""){
                alert('Unerlaubt Zeichen im Suchbegriff: ' + forbiddenChars);
                return false;
        }

        if(!prepareSearch()) return false;

        document.getElementById("-searchbox").value = "";

        return true;
}


function prepareSearch(){
        //dont submit on empty searchbox
        if(document.getElementById("-searchbox").value == "") return false;

        //add term to breadcrumbs (checks weather breadcrumb allready exists
        if(!addBreadcrumb("searchterm", document.getElementById("-searchbox").value)) return false;

        //add term to hidden form field searchterms
        //use ; for separation of terms
        document.getElementById("-searchterms").value += document.getElementById("-searchbox").value + ';';

        return true;
}

//add a new breadcrumb
//text appears in the breadcrumb
//type can be "searchterm", "publisher", "issued", "modified", "format", "theme", "region"
//returns true if successfull

function addBreadcrumb(type, text){

        //check if that breadcrumb allready exists
        if(document.getElementById("-" + text) !== null) return false;

        //create new div and button element and fill them
        var bcDiv = document.createElement("div");
        var bcBold = document.createElement("b");
        var bcBtn = document.createElement("button");
        bcDiv.className = "breadcrumb";
        bcDiv.id = text;
        bcBtn.className = "x-btn";
        bcBtn.type = "button";

        var textBtn = document.createTextNode("x");
        var textDiv;
        var textBold;

        if(type == "publisher"){
                bcDiv.style = "background-color: #F0F0C8";
                bcBtn.addEventListener('click', function() { removeBreadcrumb("publisher", text); });

                textBold = document.createTextNode(PUBLISHERS);
                textDiv = document.createTextNode(' > ' + text + ' ');
        }

        if(type == "issued"){
                bcDiv.style = "background-color: #F0C8F0";
                bcBtn.addEventListener('click', function() { removeBreadcrumb("issued", text); });
                textBold = document.createTextNode(ISSUED);
                textDiv = document.createTextNode(' > ' + text + ' ');
        }

        if(type == "format"){
                bcDiv.style = "background-color: #C8F0C8";
                bcBtn.addEventListener('click', function() { removeBreadcrumb("format", text); });

                textBold = document.createTextNode(FORMAT);
                textDiv = document.createTextNode(' > ' + text + ' ');
        }

       if(type == "region"){
                bcDiv.style = "background-color: F0C8C8";
                bcBtn.addEventListener('click', function() { removeBreadcrumb("region", text); });

                textBold = document.createTextNode(REGION);
                textDiv = document.createTextNode(' > ' + text + ' ');
        }

        if(type == "theme"){
                bcDiv.style = "background-color: #E1E1E1";
                bcBtn.addEventListener('click', function() { removeBreadcrumb("theme", text); });

                textBold = document.createTextNode(THEME);
                textDiv = document.createTextNode(' > ' + text + ' ');
        }


        //modified needs a different id since it has the same labels as issued
        if(type == "modified"){
                bcDiv.id = "+" + text;
                bcDiv.style = "background-color: #DCDCFE";
                bcBtn.addEventListener('click', function() { removeBreadcrumb("modified", text); });
                textBold = document.createTextNode(MODIFIED);
                textDiv = document.createTextNode(' > ' + text + ' ');
        }

        //searchterm has a different id so that no mixup occurrs when a user enters the name of a facet as a searchterm
        if(type == "searchterm"){
                bcDiv.id = "-" + text;
                bcBtn.addEventListener('click', function() { removeBreadcrumb("searchterm", text); });
                textBold = document.createTextNode('Suchterm');
                textDiv = document.createTextNode(' > "' + text + '" ');
        }
        //add bold to div
        bcDiv.appendChild(bcBold);

        //add text to div and button
        bcDiv.appendChild(textDiv);
        bcBtn.appendChild(textBtn);
        bcBold.appendChild(textBold);

        //add button to div
        bcDiv.appendChild(bcBtn);

        //add div to breadcrumbs well
        document.getElementById("-breadcrumbs").appendChild(bcDiv);
        return true;
}

//remove a breadcrumb specified by its id
//type can be "searchterm", "publisher", "issued", "modified", "format", "theme", "region"
//reload datasets
function removeBreadcrumb(type, id){

        if(type == "searchterm"){
                //remove searchterm from list of searchterms in form
                document.getElementById("-searchterms").value = document.getElementById("-searchterms").value.replace(id + ";", "");

                //remove visual breadcrumb
                document.getElementById("-breadcrumbs").removeChild(document.getElementById("-" + id));
        }else if(type == "publisher"){
                //slice out cathegory
                var publ = "";
                if(id.indexOf(REGIONAL_INSTITUTIONS) == 0) publ = id.slice((REGIONAL_INSTITUTIONS.length + 3));
                if(id.indexOf(STATE_INSTITUTIONS) == 0) publ = id.slice((STATE_INSTITUTIONS.length + 3));
                if(id.indexOf(PRIVATE_INSTITUTIONS) == 0) publ = id.slice((PRIVATE_INSTITUTIONS.length + 3));
                if(publ === "") publ = id;

                //remove publisher from list of publishers
                document.getElementById("-publishers").value = document.getElementById("-publishers").value.replace(publ + ";", "");

                //remove visual breadcrumb
                document.getElementById("-breadcrumbs").removeChild(document.getElementById(id));
        }else if(type == "issued"){
                //remove date from list of issued dates in form
                document.getElementById("-issued").value = document.getElementById("-issued").value.replace(id + ";", "");

                //remove visual breadcrumb
                document.getElementById("-breadcrumbs").removeChild(document.getElementById(id));
        }else if(type == "modified"){
                //remove date from list of modified dates in form
                document.getElementById("-modified").value = document.getElementById("-modified").value.replace(id + ";", "");

                //remove visual breadcrumb
                document.getElementById("-breadcrumbs").removeChild(document.getElementById("+" + id));
        }else if(type == "format"){
                //slice out cathegory
                var form = "";
                if(id.indexOf(GEO) == 0) form = id.slice((GEO.length + 3));
                if(id.indexOf(STRUCTURED) == 0) form = id.slice((STRUCTURED.length + 3));
                if(id.indexOf(TEXT) == 0) form = id.slice((TEXT.length + 3));
                if(id.indexOf(PICTURE) == 0) form = id.slice((PICTURE.length + 3));
                if(id.indexOf(OTHER) == 0) form = id.slice((OTHER.length + 3));


                if(form === "") form = id;

                //remove format from formatlist in form
                document.getElementById("-format").value = document.getElementById("-format").value.replace(form + ";", "");

                //remove visual breadcrumb
                document.getElementById("-breadcrumbs").removeChild(document.getElementById(id));

        }else if(type == "region"){
                //remove region from list of regions in form
                document.getElementById("-region").value = document.getElementById("-region").value.replace(id + ";", "");

                //remove visual breadcrumb
                document.getElementById("-breadcrumbs").removeChild(document.getElementById(id));
        }else if(type == "theme"){
                //remove region from list of regions in form
                document.getElementById("-theme").value = document.getElementById("-theme").value.replace(id + ";", "");

                //remove visual breadcrumb
                document.getElementById("-breadcrumbs").removeChild(document.getElementById(id));
        }


        //resubmit form to update datasets
        document.getElementById("-search").submit();

}

//called when a node(facet) of the navigational tree is selected
function selectTreeNode(node){
        var ParentText = $('#-tree').treeview('getParent', node).text;

        //a publisher was selected
        if(ParentText == PUBLISHERS || ParentText == REGIONAL_INSTITUTIONS || ParentText == STATE_INSTITUTIONS || ParentText == PRIVATE_INSTITUTIONS){
                //if facet has been selected allready do nothing
                if(document.getElementById("-publishers").value.indexOf(node.text) > -1) return;

                //add selection to publisherlist, create breadcrumb
                document.getElementById("-publishers").value += node.text + ";";
                if(ParentText != PUBLISHERS){
                        addBreadcrumb("publisher", ParentText + " > " + node.text);
                }else{
                        addBreadcrumb("publisher", node.text);
                }

        }

        //a time of issue was selected
        if(ParentText == ISSUED){
                //if facet has been selected allready do nothing
                if(document.getElementById("-issued").value.indexOf(node.text) > -1) return;

                //add selection to issued list, create breadcrumb
                document.getElementById("-issued").value += node.text + ";";
                addBreadcrumb("issued", node.text);
        }

        //a time os last modification was selected
        if(ParentText == MODIFIED){
                //if facet has been selected allready do nothing
                if(document.getElementById("-modified").value.indexOf(node.text) > -1) return;

                //add selection to modified list, create breadcrumb
                document.getElementById("-modified").value += node.text + ";";
                addBreadcrumb("modified", node.text);
        }

        //a format was selected
        if(ParentText == FORMAT || ParentText == GEO || ParentText == PICTURE || ParentText == TEXT || ParentText == OTHER || ParentText == STRUCTURED){
                //if facet has been selected allready do nothing
                if(document.getElementById("-format").value.indexOf(node.text) > -1) return;

                //add selection to format list, create breadcrumb
                document.getElementById("-format").value += node.text + ";";
                if(ParentText != FORMAT){
                        addBreadcrumb("format", ParentText + " > " + node.text);
                }else{
                        addBreadcrumb("format", node.text);

                }
        }

        //a region was selected
        if(ParentText == REGION){
                //if facet has been selected allready do nothing
                if(document.getElementById("-region").value.indexOf(node.text) > -1) return;

                //add selection to region list, create breadcrumb
                document.getElementById("-region").value += node.text + ";";
                addBreadcrumb("region", node.text);
        }

        //a region was selected
        if(ParentText == THEME){
                //if facet has been selected allready do nothing
                if(document.getElementById("-theme").value.indexOf(node.text) > -1) return;

                //add selection to theme list, create breadcrumb
                document.getElementById("-theme").value += node.text + ";";
                addBreadcrumb("theme", node.text);
        }



        //submit form to update datasets
        document.getElementById("-search").submit();
}

</script>


  </head>
  <body>

<?php

function printPublishers($publisherGroup) {
        $file = fopen("publishers.csv", "r") or die("Can't open publishers.csv");
        while(!feof($file)) {
            $row = fgetcsv($file,100, ";");
                if($row[1] == $publisherGroup)
                        echo '{ text: "' . $row[0] . '" },';
        }
        fclose($file);
}

function printFormats($formatGroup) {
        $file = fopen("formats.csv", "r") or die("Can't open publishers.csv");
        while(!feof($file)) {
                $row = fgetcsv($file,100, ";");
                if($row[1] == $formatGroup)
                        echo '{ text: "' . $row[0] . '" },';
        }
        fclose($file);
}

function printRegions() {
        $file = fopen("regions.csv", "r") or die("Can't open publishers.csv");
        while(!feof($file)) {
                $row = fgetcsv($file,100, ";");
                if($row[0] != "region") echo '{ text: "' . $row[0] . '" },';
        }
        fclose($file);
}

?>



  <div class="container-fluid">
        <div class="jumbotron">
                <img src="http://www.wu.ac.at/typo3conf/ext/wu_template/Resources/Public/Images/logo.png" style="float: right;">
                <h1>Data Finder</h1>
                <p>Facettensuche über alle österreichischen Open Data Portale. </p>
                <small style="float: right">2016 erstellt von Georg Prohaska <br> im Zuge einer Bachelorarbeit</small>
        </div>

        <center>
                <form class="form-inline" role="form" id="-search" method="GET" action="results.php" target="results" onsubmit="return validateSearch()">
                <input class="form-control" type="text" name="searchbox" id="-searchbox" placeholder="Suchbegriff hinzufügen"></input>
                <input type="hidden" name="searchterms" id="-searchterms" value="">
                <input type="hidden" name="publishers" id="-publishers" value="">
                <input type="hidden" name="issued" id="-issued" value="">
                <input type="hidden" name="modified" id="-modified" value="">
                <input type="hidden" name="format" id="-format" value="">
                <input type="hidden" name="region" id="-region" value="">
                <input type="hidden" name="theme" id="-theme" value="">
                <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Suchen</button>
                </form>
        </center>
        <hr>
        <div class="well well" id="-breadcrumbs">
        Gesucht wird:
        </div>

        <div class="row">
         <div class="col-md-3">
         <div id="-tree">
                <script>
                var tree = [
                {
                        text: PUBLISHERS,
                        selectable: false,
                        nodes: [
                         {
                                text: REGIONAL_INSTITUTIONS,
                                nodes: [
                                        <?php printPublishers("ra"); ?>
                                ]
                        },
                        {
                                text: STATE_INSTITUTIONS,
                                nodes: [
                                        <?php printPublishers("si"); ?>
                                ]
                        },
                        {
                                text: PRIVATE_INSTITUTIONS,
                                nodes: [
                                        <?php printPublishers("pi"); ?>
                                ]
                        }
                        ]
                },
                {
                        text: ISSUED,
                        selectable: false,
                        nodes: [
                        {
                                text: YEAR_2016
                        },

                        {
                                text: YEAR_2015
                        },

                        {
                                text: YEAR_2014
                        },

                        {
                                text: YEAR_2013
                        },


                        {
                                text: YEAR_2012
                        }]

                },
                {
                        text: MODIFIED,
                        selectable: false,
                        nodes: [
                        {
                                text: YEAR_2016
                        },

                        {
                                text: YEAR_2015
                        },

                        {
                                text: YEAR_2014
                        }]

                },
                {
                        text: FORMAT,
                        selectable: false,
                        nodes: [
                         {
                                text: GEO,
                                nodes: [
                                        <?php printFormats("geo"); ?>
                                ]
                        },
                        {
                                text: PICTURE,
                                nodes: [
                                        <?php printFormats("pic"); ?>
                                ]
                        },
                        {
                                text: STRUCTURED,
                                nodes: [
                                        <?php printFormats("structured"); ?>
                                ]
                        },

                        {
                                text: TEXT,
                                nodes: [
                                        <?php printFormats("text"); ?>
                                ]
                        },
                        {
                                text: OTHER,
                                nodes: [
                                        <?php printFormats("other"); ?>
                                ]
                        }]

                },
                {
                        text: THEME,
                        selectable: false,
                        nodes: [
                        {
                                text: FINANCE
                        },
                        {
                                text: PEOPLE
                        },
                        {
                                text: ENVIRONMENT
                        },
                        {
                                text: EDUCATION
                        },
                        {
                                text: HEALTH
                        },
                        {
                                text: ECONOMY
                        },
                        {
                                text: ART
                        },
                        {
                                text: POLITICS
                        },
                        {
                                text: GEOGRAPHY
                        }
                        ]
                },
                {
                        text: REGION,
                        selectable: false,
                        nodes: [
                                <?php printRegions(); ?>
                        ]
                }
                ];
                $('#-tree').treeview({data: tree, multiSelect: false, onNodeSelected: function (event, node) { selectTreeNode(node); }});
                $('#-tree').treeview('collapseAll', { silent: true });
         </script>
         </div><!-- tree -->
         </div><!-- col -->
        <div class="col-md-8">
         <iframe src="results.php" id="iFrameId" name="results" style="border:none" width="100%" height="100%"></iframe>
        <script>

        </script>
        </div><!-- col -->
        </div> <!-- row -->
  </div>
  </body>
</html>



