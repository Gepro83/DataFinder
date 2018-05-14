<html lang="en">
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
  </head>

<body>

<?php
require_once( "sparqllib.php" );

//Globals

//Names of facet cathegories, MUST match Javascript globals in index.php
$PUBLISHERS = "Datenlieferanten";
$REGIONAL_INSTITUTIONS = "Regionale Stellen";
$STATE_INSTITUTIONS = "Staatliche Stellen";
$PRIVATE_INSTITUTIONS = "Private Insititutionen";

$ISSUED = "Erstellungsdatum";
$THIS_WEEK = "Diese Woche";
$THIS_MONTH = "Dieser Monat";
$YEAR_2016 = "Jahr 2016";
$YEAR_2015 = "Jahr 2015";
$YEAR_2014 = "Jahr 2014";
$YEAR_2013 = "Jahr 2013";
$YEAR_2012 = "Jahr 2012";

$FORMAT = "Format";
$GEO = "Geoinformation";
$PICTURE = "Bild";
$STRUCTURED ="Strukturiert";
$TEXT = "Text";
$OTHER = "Andere";

$THEME = "Thema";
$FINANCE = "Finanzen";
$PEOPLE = "Menschen";
$ENVIRONMENT = "Umwelt";
$EDUCATION = "Bildung";
$HEALTH = "Gesundheit";
$ECONOMY = "Wirtschaft";
$ART = "Kunst und Kultur";
$POLITICS = "Politik und Recht";
$GEOGRAPHY = "Geographie";



function execQuery($query) {

        $data = sparql_get(
                        "http://opendatasearch.ai.wu.ac.at:8890/sparql", $query);

        if( !isset($data) )
        {
                echo "Error: " . sparql_errno(). ": " . sparql_error();
                return false;
        }
        return $data;

}

//removes html tags, special chars, line breaks and single quotiations
function cleanString($str){
        return str_replace("\\", "-", str_replace("'", "", strip_tags(stripslashes(htmlspecialchars(preg_replace( "/\r|\n/", "", $str))))));
}

function getDatasets($query){
        $data = execQuery($query);

        for($row = 0; $row < count($data); $row++) {
                //if description is too long, cut it
                if(strlen($data[$row]["description"]) > 200) $data[$row]["description"] = (substr($data[$row]["description"], 0, 200) . "...");

                //create new array entry for every dataset
                echo "\n datasets.push({url:'" . $data[$row]["dataset"] . "', title:'" . cleanString($data[$row]["title"]) . "', "
                        . "description:'" . cleanString($data[$row]["description"]) . "', publisher:'" . $data[$row]["publisher"] . "', "
                        . "issued:'" . $data[$row]["issued"] . "', modified:'" . $data[$row]["modified"] . "', "
                        . "formats:'" . $data[$row]["format"] . "'});";


        }
}

//returns string containing FILTER part of SPARQL query
//filters check if search terms are contained in ?all column
function getFilters(){
        //add searchterms
        $token = strtok($_GET["searchterms"], ';');
        $filters = "";

        //create filter for every searchterm
        while($token !== false){
                $filters .= ' FILTER CONTAINS(lcase(?all), "' . trim(mb_strtolower($token, "UTF-8")) . '")';
                $token = strtok(';');
        }


        //add selected publishers
        global $REGIONAL_INSTITUTIONS, $STATE_INSTITUTIONS, $PRIVATE_INSTITUTIONS;
        $token = strtok($_GET["publishers"], ';');

        while($token !== false){
                if($token === $REGIONAL_INSTITUTIONS ||
                                $token === $STATE_INSTITUTIONS ||
                                $token ===  $PRIVATE_INSTITUTIONS){
                        $filters .= getPublishergroupFilters($token);
                }else{
                        $filters .= ' FILTER CONTAINS(lcase(?publisher), "' . trim(mb_strtolower($token, "UTF-8")) . '")';
                }
                $token = strtok(';');
        }

        //add selected issued year
        global $YEAR_2012, $YEAR_2013, $YEAR_2014, $YEAR_2015, $YEAR_2016;
        $token = strtok($_GET["issued"], ';');

        while($token !== false){
                $year = "";
                if($token === $YEAR_2012) $year = "2012";
                if($token === $YEAR_2013) $year = "2013";
                if($token === $YEAR_2014) $year = "2014";
                if($token === $YEAR_2015) $year = "2015";
                if($token === $YEAR_2016) $year = "2016";

                $filters .= ' FILTER (year(?issued) = ' . $year . ')';
                $token = strtok(';');
        }

        //add selected modified year
        $token = strtok($_GET["modified"], ';');

        while($token !== false){
                $year = "";
                if($token === $YEAR_2012) $year = "2012";
                if($token === $YEAR_2013) $year = "2013";
                if($token === $YEAR_2014) $year = "2014";
                if($token === $YEAR_2015) $year = "2015";
                if($token === $YEAR_2016) $year = "2016";

                $filters .= ' FILTER (year(?modified) = ' . $year . ')';
                $token = strtok(';');
        }

        //add selected formats
        global $GEO, $STRUCTURED, $TEXT, $PICTURE, $OTHER;
        $token = strtok($_GET["format"], ';');

        while($token !== false){
                if($token === $GEO ||
                   $token === $STRUCTURED ||
                   $token === $TEXT ||
                   $token === $PICTURE ||
                   $token === $OTHER){
                        $filters .= getFormatgroupFilters($token);
                }else{
                        $filters .= ' FILTER CONTAINS(lcase(?format), "' . trim(mb_strtolower($token, "UTF-8")) . '")';
                }
                $token = strtok(';');
        }

        //add selected regions
        $token = strtok($_GET["region"], ';');

        while($token !== false){
                $file = fopen("regions.csv", "r") or die("Can't open regions.csv");

                while(!feof($file)) {
                        $row = fgetcsv($file, 100, ";");
                        if($row[0] != "region"){
                                if($row[0] == $token){
                                        $filters .= ' FILTER (CONTAINS(lcase(?all), "' . trim(mb_strtolower($token, "UTF-8")) . '") ||';
                                        $filters .= ' CONTAINS(lcase(?all), "' . trim(mb_strtolower($row[1], "UTF-8")) . '") )';
                                }
                        }
                }
                $token = strtok(';');
                fclose($file);
        }

        //add selected theme
        global $FINANCE, $PEOPLE, $ENVIRONMENT, $EDUCATION, $HEALTH, $ECONOMY, $ART, $POLITICS, $GEOGRAPHY;
        $token = strtok($_GET["theme"], ';');

        while($token !== false){
                $file = fopen("themes.csv", "r") or die("Can't open themes.csv");
                $theme = "";

                //translate to csv encoding
                if($token === $FINANCE) $theme = "finance";
                if($token === $PEOPLE) $theme = "people";
                if($token === $ENVIRONMENT) $theme = "environment";
                if($token === $EDUCATION) $theme = "education";
                if($token === $HEALTH) $theme = "health";
                if($token === $ECONOMY) $theme = "economy";
                if($token === $ART) $theme = "art";
                if($token === $POLITICS) $theme = "politics";
                if($token === $GEOGRAPHY) $theme = "geography";

                $filters .= "FILTER (";

                while(!feof($file)) {
                        $row = fgetcsv($file, 100, ";");
                        if($row[0] != "keyword"){
                                if($row[1] === $theme)
                                        $filters .= 'CONTAINS(lcase(?kws), "' . trim(mb_strtolower($row[0], "UTF-8")) . '") || ';
                        }
                }

                $filters = substr($filters, 0, (strlen($filters) - 4)) . ") ";

                $token = strtok(';');
                fclose($file);

        }

        return $filters;
}

//returns a string that contains SPARQL filter statements for the 3 Publisher groups
function getPublishergroupFilters($publisherGroup){
        //translate internal publishergroup name to encoding of csv file
        global $REGIONAL_INSTITUTIONS, $STATE_INSTITUTIONS, $PRIVATE_INSTITUTIONS;

        if($publisherGroup == $REGIONAL_INSTITUTIONS) $publisherGroup = "ra";
        if($publisherGroup == $STATE_INSTITUTIONS) $publisherGroup = "si";
        if($publisherGroup == $PRIVATE_INSTITUTIONS) $publisherGroup = "pi";

        $ret = "FILTER (";
        //open list of publishers
        $file = fopen("publishers.csv", "r") or die("Can't open publishers.csv");
        while(!feof($file)) {
                $row = fgetcsv($file,100, ";");
                if($row[1] == $publisherGroup)
                $ret .= 'contains(lcase(?publisher), "' . trim(mb_strtolower($row[0], "UTF-8")) . '") || ';
        }
        fclose($file);
        $ret = substr($ret, 0, (strlen($ret) - 4)) . ") ";
        return $ret;
}

//returns a string that contains SPARQL filter statements for the 5 format groups
function getFormatgroupFilters($formatGroup){
        //translate internal formatgroup name to encoding of csv file
        global $GEO, $STRUCTURED, $TEXT, $PICTURE, $OTHER;

        if($formatGroup == $GEO) $formatGroup = "geo";
        if($formatGroup == $STRUCTURED) $formatGroup = "structured";
        if($formatGroup == $TEXT) $formatGroup = "text";
        if($formatGroup == $PICTURE) $formatGroup = "pic";
        if($formatGroup == $OTHER) $formatGroup = "other";


        $ret = "FILTER (";
        //open list of formats
        $file = fopen("formats.csv", "r") or die("Can't open formats.csv");
        while(!feof($file)) {
                $row = fgetcsv($file,100, ";");
                if($row[1] == $formatGroup)
                $ret .= 'contains(lcase(?format), "' . trim(mb_strtolower($row[0], "UTF-8")) . '") || ';
        }
        fclose($file);
        $ret = substr($ret, 0, (strlen($ret) - 4)) . ") ";
        return $ret;
}
?>


<div class="container-fluid">
<h4><b id="datasetCount">
<?php /* echo execQuery('
                PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                PREFIX dcat: <http://www.w3.org/ns/dcat#>
                PREFIX dct: <http://purl.org/dc/terms/>

                SELECT count(*) as ?count
                {
                ?d a dcat:Dataset .
                ?d dct:title ?title .
                ?d dct:description ?description .
                ?d dct:publisher ?p .
                ?p foaf:name ?publisher .
                ?d dct:issued ?issued .
                ?d dct:modified ?modified

                {
                SELECT ?d, concat(group_concat(?k ; separator = " / "), " ", ?t, " ", ?de) as ?all, group_concat(?k; separator =" / ") as ?kws
                WHERE {
                ?d dcat:keyword ?k .
                ?d dct:title ?t .
                ?d dct:description ?de .
                }group by ?d ?t ?de
                }

                {
                SELECT ?d, group_concat(distinct ?f ; separator =" / ") as ?format
                WHERE{
                        ?d dcat:distribution ?di .
                        ?di dct:format ?f
                }
                }

                ' . getFilters() . '
                }
                ')[0]["count"];*/
?>
</b> Datensätze gefunden</h4>

<ul class="list-group" id="listGroup">
</ul>

<nav>
<center>
<ul class="pagination" id="pages">
 <li id="pages-prev" class="disabled">
  <a href="#" role="button" aria-label="Previous" onclick="clickedPrev()">
   <span aria-hidden="true">&laquo;</span>
  </a>
 </li>
 <li id="pages-next" class="disabled">
  <a href="#" role="button" aria-label="Next" onclick="clickedNext()">
   <span aria-hidden="true">&raquo;</span>
  </a>
 </li>
</ul>
</center>
</nav>
</div>
<script>
var datasets = [];


<?php getDatasets('
                PREFIX foaf: <http://xmlns.com/foaf/0.1/>
                PREFIX dcat: <http://www.w3.org/ns/dcat#>
                PREFIX dct: <http://purl.org/dc/terms/>

                SELECT ?dataset, ?title, ?description, ?publisher, concat(day(?issued), ".", month(?issued), ". ", year(?issued)) as ?issued,
                       concat(day(?modified), ".", month(?modified), ". ", year(?modified)) as ?modified, ?format
                {
                ?dataset a dcat:Dataset .
                ?dataset dct:title ?title .
                ?dataset dct:description ?description .
                ?dataset dct:publisher ?p .
                ?p foaf:name ?publisher .
                ?dataset dct:issued ?issued .
                ?dataset dct:modified ?modified .

                {
                SELECT ?dataset, concat(group_concat(?k ; separator = " / "), " ", ?t, " ", ?de) as ?all, group_concat(?k; separator =" / ") as ?kws
                WHERE {
                ?dataset dcat:keyword ?k .
                ?dataset dct:title ?t .
                ?dataset dct:description ?de .
                }group by ?dataset ?t ?de
                }

                {
                SELECT ?dataset, group_concat(distinct ?f ; separator =" / ") as ?format
                WHERE{
                        ?dataset dcat:distribution ?di .
                        ?di dct:format ?f
                }
                }

                ' . getFilters() . '
                }
        ');
?>
document.getElementById("datasetCount").innerHTML = datasets.length;

//--Add pagination--

//8 Datasets per page
var pagesCount = Math.ceil(datasets.length / 5);

//display page 1 to start
var currentPage = 1;
displayPage(1);



//get pageination list and next button
var list = document.getElementById("pages");
var next = document.getElementById("pages-next");

//index start at page 1
var MyCurrentStartPage = 1;
createPages(1);
showFirstPage();

//create 5 pages starting from page "start"
function createPages(start){
        //remove all pages
        while(list.hasChildNodes()){
                list.removeChild(list.childNodes[0]);
        }

        //add previous button
        var prev  = document.createElement("li");
        prev.id = "pages-prev";
        var a1 = document.createElement("a");
        a1.setAttribute("role", "button");
        a1.setAttribute("aria-label", "Previous");
        a1.addEventListener('click', function() { clickedPrev(); });
        var span1 = document.createElement("span");
        span1.setAttribute("aria-hidden", "true");
        span1.innerHTML = "&laquo;";

        a1.appendChild(span1);
        prev.appendChild(a1);
        list.appendChild(prev);

        //ad next button
        next = document.createElement("li");
        next.id = "pages-next";
        var a2 = document.createElement("a");
        a2.setAttribute("role", "button");
        a2.setAttribute("aria-label", "Next");
        a2.addEventListener('click', function() { clickedNext(); });
        var span2 = document.createElement("span");
        span2.setAttribute("aria-hidden", "true");
        span2.innerHTML = "&raquo;";

        a2.appendChild(span2);
        next.appendChild(a2);
        list.appendChild(next);


        //enable/disable previous and next buttons if necessary
        if(start == 1){ document.getElementById("pages-prev").className = "disabled"; }
        else { document.getElementById("pages-prev").className = ""; }
        if((start + 5) > pagesCount) { document.getElementById("pages-next").className = "disabled"; }
        else { document.getElementById("pages-next").className = ""; }

        for (var pageNumber = start; pageNumber <= pagesCount; pageNumber++){

                //create new list entry, and link element
                var page = document.createElement("li");
                var link = document.createElement("a");
                var linkText = document.createTextNode(pageNumber);

                link.appendChild(linkText);
                link.id = pageNumber.toString()
                link.setAttribute("role", "button");

                link.addEventListener('click', function() { clickedPage(event); });

                page.appendChild(link);

                //add page to pageination
                list.insertBefore(page, next);

                //if maximum number of pages (5) is reached stop loop
                if(pageNumber == (start + 4)) pageNumber = pagesCount;
        }
}

//show the first page of the new set by simulating a click event
function showFirstPage(){
        var event;
        if (document.createEvent) {
                event = document.createEvent("MouseEvent");
                event.initEvent("click", true, true);
        } else {
                event = document.createEventObject();
                event.eventType = "click";
        }

        event.eventName = "click";

        if (document.createEvent) {
                document.getElementById(MyCurrentStartPage).dispatchEvent(event);
        } else {
                document.getElementById(MyCurrentStartPage).fireEvent("on" + event.eventType, event);
        }
}

function clickedPrev(){
        if(document.getElementById("pages-prev").className == "disabled") return;

        //view the next 5 pages
        MyCurrentStartPage -= 5;
        createPages(MyCurrentStartPage);
        showFirstPage();
}

function clickedNext(){
        if(document.getElementById("pages-next").className == "disabled") return;

        MyCurrentStartPage += 5;
        createPages(MyCurrentStartPage);
        showFirstPage();
}

function clickedPage(event){
        var item = event.currentTarget;

        if(document.getElementById(currentPage)) document.getElementById(currentPage).style = "background-color: white";
        currentPage = item.id;
        displayPage(item.id);

        item.style = "background-color: rgb(200, 200, 200)";

}

function displayPage(page){
        //clear listgroup
        listGroup = document.getElementById("listGroup");

        while(listGroup.hasChildNodes()){
                listGroup.removeChild(listGroup.childNodes[0]);
        }
        //add up to 5 list items
        for(listItem = 0; listItem < 5; listItem++){
                var index = (5 * (page - 1) + listItem);

                if(datasets[index] != null){
                        var li = document.createElement("li");
                        li.className = "list-group-item";

                        var heading = document.createElement("h5");
                        heading.className = "list-group-item-heading";

                        var b = document.createElement("b");

                        var a = document.createElement("a");
                        a.href = datasets[index]["url"];
                        var aText = document.createTextNode(datasets[index]["title"]);

                        var p1 = document.createElement("p");
                        p1.className = "list-group-item-text";
                        var p1Text = document.createTextNode(datasets[index]["description"]);

                        var table = document.createElement("table");
                        table.style = "width:100%;font-size: 14px;line-height: 1.3;";

                        var tr1 = document.createElement("tr");
                        var tr2 = document.createElement("tr");
                        var td1 = document.createElement("td");
                        var td2 = document.createElement("td");
                        var td3 = document.createElement("td");
                        var td4 = document.createElement("td");

                        table.appendChild(tr1);
                        table.appendChild(tr2);

                        tr1.appendChild(td1);
                        tr1.appendChild(td2);
                        tr2.appendChild(td3);
                        tr2.appendChild(td4);

                        var b1 = document.createElement("b");
                        var b2 = document.createElement("b");
                        var b3 = document.createElement("b");
                        var b4 = document.createElement("b");

                        b1.appendChild(document.createTextNode("Datenlieferant: "));
                        b2.appendChild(document.createTextNode("Erstellungsdatum: "));
                        b3.appendChild(document.createTextNode("Letztes Update: "));
                        b4.appendChild(document.createTextNode("Formate: "));

                        td1.appendChild(b1);
                        td1.appendChild(document.createTextNode(datasets[index]["publisher"]));
                        td2.appendChild(b2);
                        td2.appendChild(document.createTextNode(datasets[index]["issued"]));
                        td3.appendChild(b3);
                        td3.appendChild(document.createTextNode(datasets[index]["modified"]));
                        td4.appendChild(b4);
                        td4.appendChild(document.createTextNode(datasets[index]["formats"]));

                        a.appendChild(aText);
                        p1.appendChild(p1Text);

                        b.appendChild(a);
                        heading.appendChild(b);
                        li.appendChild(heading);
                        li.appendChild(p1);
                        li.appendChild(document.createElement("br"));
                        li.appendChild(table);
                        listGroup.appendChild(li);
                }
        }
}

</script>
</body>
</html>


