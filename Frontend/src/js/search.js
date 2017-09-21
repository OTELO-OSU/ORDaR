  var APP = (function() {
      return {
          modules: {}
      }
  })();
  // Module datatable, Affichage des resultats sous forme de pagination
  APP.modules.datatable = (function() {
      return {
          AppendTable: function(data, facets, query, current, module) {
              if (data['hits']['total'] == 0 || data['hits']['total'] == null) {
                  $('#facets').hide();
                  if (facets) {
                      $('#facets').show();
                  }
                  $('#results').empty();
                  $('#info-noresult').empty();
                  $('#info-noresult').append('No result found!');
                  $('#info-noresult').addClass('red');
                  $('#info-noresult').show();
                  $('#info').hide();
                  $('.pagination').hide();
                  $('#gridlogo').show();
                  $('.pagination').empty();
              } else {
                  $('#results').empty();
                  $('.pagination').empty();
                  $('.pagination').show();
                  $('#info-noresult').empty();
                  $('#info').empty();
                  total = data['hits']['total'];
                  data = data['hits']['hits'];
                  for (var k in data) {
                      var authorsname;
                      authorsname = undefined;
                      title = data[k]['TITLE'];
                      data_description = data[k]['DATA_DESCRIPTION'];
                      filetype = data[k]['FILETYPE'];
                      accessright = data[k]['ACCESS_RIGHT'];
                      accessrightdate = data[k]['PUBLICATION_DATE'];
                      creationdate = data[k]['CREATION_DATE'];
                      if (accessright == "Open") {
                          access = '<div class="ui green label">' + accessright + '</div>';
                      } else if (accessright == "Embargoed") {
                          access = '<div class="ui orange label" data-tooltip="Available as Open Access after ' + accessrightdate + '">' + accessright + '</div>';
                      } else if (accessright == "Closed") {
                          access = '<div class="ui red label">' + accessright + '</div>';
                      } else if (accessright == "Unpublished") {
                          access = '<div class="ui yellow label">' + accessright + '</div>';
                      } else if (accessright == "Draft") {
                          access = '<div class="ui grey label">' + accessright + '</div>';
                      }
                      file_creator = data[k]['FILE_CREATOR'];
                      id = data[k]['_id'];
                      type = data[k]['_type'];
                      uploaddate = data[k]['UPLOAD_DATE'];
                      id = id;
                      for (var k in file_creator) {
                          authorsname = [];
                          if (file_creator.length >= 1) {
                              for (var k in file_creator) {
                                  author = file_creator[k]["NAME"] + " " + file_creator[k]["FIRST_NAME"];
                                  authorsname.push(author);
                              }
                          }
                      }
                      var authorsnames = jQuery("<ul></ul>");
                      for (k in authorsname) {
                          var li = jQuery("<li></li>");
                          li.append(authorsname[k]);
                          authorsnames.append(li);
                      }
                      $('#results').append('<div class="item"> <div class="content">' + access + '<div class="ui blue label" data-tooltip="Creation date" >' + creationdate + '</div><div class="row"></div><a href="record?id=' + id + '" class="header">' + title + '</a><div class="meta"><h4>Description:</h4><span class="data">' + data_description + '</span></div><div class="row"></div><div class="author"><h4><i class="user icon"></i>Authors:</h4><ul>' + authorsnames.html() + '</ul></div><div class="extra"> <a href="record?id=' + id + '"><div class="ui right floated primary button" >View<i class="right chevron icon"></i></div></a></div><p>Uploaded on ' + uploaddate + '</p></div>');
                  }
                  $('#info').append(total + ' result(s) found!');
                  $('#info').addClass('green');
                  $('#info').show();
                  $('#info-noresult').hide();
                  $('#logosearch').hide();
                  $('#gridlogo .row').remove();
                  nbpages = (total / 10);
                  nbpages = Math.ceil(nbpages);
                  if (current != 0) {
                      current = current.text;
                  } else {
                      current = 1;
                  }
                  if (nbpages == 1) {
                      $(".pagination").append('<a class="item page-1" href="javascript:void(0);">1</a>');
                  } else {
                      $(".pagination").append('<a class="item page-1" href="javascript:void(0);">1</a>');
                      if ((current != 1) && (current != 2)) {
                          $(".pagination").append('<div class="disabled item"> ... </div>');
                      }
                      for (var i = parseInt(current) - 1; i < parseInt(current); i++) {
                          if (i > 1) {
                              $(".pagination").append('<a class="item page-' + i + '"  href="javascript:void(0);">' + i + '</a>');
                          }
                      }
                      for (var i = parseInt(current); i < parseInt(current) + 2; i++) {
                          if ((i < nbpages) && (i > 1)) {
                              $(".pagination").append('<a class="item page-' + i + '" href="javascript:void(0);">' + i + '</a>');
                          }
                      }

                      $(".pagination").append('<div class="disabled item"> ... </div>');

                      $(".pagination").append('<a class="item page-' + nbpages + '" href="javascript:void(0);">' + nbpages + '</a>');
                      if (module == "mypublications") {
                          $('.pagination a').on('click', function(e) {
                              APP.modules.mypublications.search(query, 'facets', this);
                              $('html,body').scrollTop(0);

                          });
                      } else {
                          $('.pagination a').on('click', function(e) {
                              APP.modules.search.search(query, 'facets', this);
                              $('html,body').scrollTop(0);

                          });
                      }
                  }

                  if (current != 0) {
                      $('.page-' + current).addClass("active");

                  } else {
                      $('.page-1').addClass("active");
                  }



              }
          }
      }
  })();
  // Module search, permet de rechercher un terme , par facets ou non
  APP.modules.search = (function() {
      return {
          //methode de recuperation d'une valeur dans l'url
          $_GET: function(param) {
              var vars = {};
              window.location.href.replace(location.hash, '').replace(/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
                  function(m, key, value) { // callback
                      vars[key] = value !== undefined ? value : '';
                  });
              if (param) {
                  return vars[param] ? vars[param] : null;
              }
              return vars;
          },
          //Methode d'intialisation de la recherche
          init: function() {
              $('#info').hide();
              $('#facets').hide();
              $('#info').removeClass('red');
              $('#info').removeClass('green');
              $('#info').empty();
              $('#results').empty();
              $('.pagination').hide();
              $('#facets_type').empty();
              var query = APP.modules.search.$_GET('query');
              if (query != null) {
                  APP.modules.search.search(query, null, "0");
              }
          },
          //Methode d'affichage des facets de la recherche
          AppendFacets: function(data) {
              if (data['aggregations'] == null) {} else {
                  sample_kind = data['aggregations']['sample_kind']['buckets'];
                  if (sample_kind.length == 0) {
                      $(".ui.card #samplekind").parent().hide();
                  }
                  $("#samplekind").append('<div class="header" >Sample kind</div>');
                  for (var k in sample_kind) {
                      count = sample_kind[k]['doc_count'];
                      type = sample_kind[k]['key'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#samplekind').append('<label title="' + longtype + '"  class="item" for="' + type + '"> <input onclick="APP.modules.search.checkCheckbox()" id="' + type + '" name="sample_kind" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  authors = data['aggregations']['authors']['buckets'];
                  if (authors.length == 0) {
                      $(".ui.card #authors").parent().hide();
                  }
                  $("#authors").append('<div class="header" >Authors</div>');
                  for (var k in authors) {
                      type = authors[k]['key'];
                      count = authors[k]['doc_count'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 25) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#authors').append('<label title="' + longtype + '"  class="item" for="' + type + 'authors"> <input onclick="APP.modules.search.checkCheckbox()" id="' + type + 'authors" name="authors" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  keywords = data['aggregations']['keywords']['buckets'];
                  if (keywords.length == 0) {
                      $(".ui.card #keywords").parent().hide();
                  }
                  $("#keywords").append('<div class="header" >Keywords</div>');
                  for (var k in keywords) {
                      count = keywords[k]['doc_count'];
                      type = keywords[k]['key'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#keywords').append('<label title="' + longtype + '"  class="item" for="' + type + 'keywords"> <input onclick="APP.modules.search.checkCheckbox()" id="' + type + 'keywords" name="keywords" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  scientific_field = data['aggregations']['scientific_field']['buckets'];
                  if (scientific_field.length == 0) {
                      $(".ui.card #scientificfield").parent().hide();
                  }
                  $("#scientificfield").append('<div class="header" >Scientific fields</div>');
                  for (var k in scientific_field) {
                      count = scientific_field[k]['doc_count'];
                      type = scientific_field[k]['key'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#scientificfield').append('<label title="' + longtype + '"  class="item" for="' + type + 'scientificfield"> <input onclick="APP.modules.search.checkCheckbox()" id="' + type + 'scientificfield" name="scientific_field" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  language = data['aggregations']['language']['buckets'];
                  if (language.length == 0) {
                      $(".ui.card #language").parent().hide();
                  }
                  $("#language").append('<div class="header" >Languages</div>');
                  for (var k in language) {
                      count = language[k]['doc_count'];
                      type = language[k]['key'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#language').append('<label title="' + longtype + '"  class="item" for="' + type + 'language"> <input onclick="APP.modules.search.checkCheckbox()" id="' + type + 'language" name="language" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  filetype = data['aggregations']['filetype']['buckets'];
                  if (filetype.length == 0) {
                      $(".ui.card #filetype").parent().hide();
                  }
                  $("#filetype").append('<div class="header" >Filetypes</div>');
                  for (var k in filetype) {
                      count = filetype[k]['doc_count'];
                      type = filetype[k]['key'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#filetype').append('<label title="' + longtype + '"  class="item" for="' + type + 'filetype"> <input onclick="APP.modules.search.checkCheckbox()" id="' + type + 'filetype" name="filetype" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  accessright = data['aggregations']['access_right']['buckets'];
                  if (accessright.length == 0) {
                      $(".ui.card #accesright").parent().hide();
                      $(".ui.card #date").parent().hide();
                  }
                  $("#accesright").append('<div class="header" >Access right</div>');
                  for (var k in accessright) {
                      count = accessright[k]['doc_count'];
                      type = accessright[k]['key'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "Open") {
                          color = "green";
                      }
                      if (type == "Closed") {
                          color = "red";
                      }
                      if (type == "Embargoed") {
                          color = "orange";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#accesright').append('<label  class="item" for="' + type + 'accessright"> <input onclick="APP.modules.search.checkCheckbox()" id="' + type + 'accessright" name="accessright" value="' + type + '" type="checkbox"> <div class="ui ' + color + ' horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  var creationdatearray = [];
                  aggs = data['aggregations'];
                  data = data['hits']['hits'];
                  $("#date").append('<div class="header" >Creation date</div>');
                  minyear = aggs['min_date']['value_as_string'].split("-", 2);
                  maxyear = aggs['max_date']['value_as_string'].split("-", 2);
                  minyear = minyear[0];
                  maxyear = maxyear[0];
                  if (minyear == maxyear) {
                      minyear = maxyear - 1;
                  }
                  $('#date').append('<input class="range-slider" value="' + minyear + ',' + maxyear + '" type="hidden">');
                  $('.range-slider').jRange({
                      from: minyear,
                      to: maxyear,
                      step: 1,
                      format: '%s',
                      width: 300,
                      showLabels: true,
                      isRange: true,
                      ondragend: function(val) {
                          var query = APP.modules.search.$_GET('query');
                          range = val.split(",", 2);
                          mindate = range[0];
                          maxdate = range[1];
                          date = " AND INTRO.CREATION_DATE:[" + mindate + "-01-01 TO " + maxdate + "-12-31]"
                          $('#results').empty();
                          if (typeof(globalfacets) != 'undefined') {
                              APP.modules.search.search(query + globalfacets + date, "facets", "0");
                          } else {
                              APP.modules.search.search(query + date, "facets", "0");
                          }
                      },
                      onbarclicked: function(val) {
                          var query = APP.modules.search.$_GET('query');
                          range = val.split(",", 2);
                          mindate = range[0];
                          maxdate = range[1];
                          date = " AND INTRO.CREATION_DATE:[" + mindate + "-01-01 TO " + maxdate + "-12-31]"
                          $('#results').empty();
                          if (typeof(globalfacets) != 'undefined') {
                              APP.modules.search.search(query + globalfacets + date, "facets", "0");
                          } else {
                              APP.modules.search.search(query + date, "facets", "0");
                          }
                      }
                  });
                  $('#facets').show();
                  $('form .ui.grid#gridresults').prepend('<div id="Displayfacets" class="ui button primary">Display/Hide facets</div>');
                  APP.modules.search.checksize();
                  $('form .ui.grid .button').on("click", function() {
                      $(".facets").toggle();
                  })
                  $(window).resize(function() {
                      APP.modules.search.checksize();

                  });
              }
          },

          checksize: function() {
              if ($(window).width() <= 1119) {
                  $('.facets').hide();
              } else {
                  $('.facets').show();
                  $('#accueil').addClass("eleven wide centered column");
              }

              if ($(window).width() <= 420) {
                  $('#accueil').removeClass();

              }

          },

          //Methode de trap des events sur les facets
          checkCheckbox: function() {
              checked = $("#facets input:checked");
              var samplekind;
              var authors;
              var keywords;
              var facets;
              var project_name;
              var scientific_field;
              var access_right;
              var filetype;
              var language;
              facets = undefined;
              $.each(checked, function(index, value) {
                  if (value.name == "accessright") {
                      var value = value.value;
                      if (access_right === undefined) {
                          access_right = 'INTRO.ACCESS_RIGHT:"' + value + '"';
                      } else {
                          access_right = access_right + ' OR INTRO.ACCESS_RIGHT:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets.replace(/ AND \([^)]*\) */g, "");
                          facets += " AND " + "(" + access_right + ")";
                      } else {
                          facets = ' AND ' + "(" + access_right + ")";
                      }
                  }
                  if (value.name == "sample_kind") {
                      var value = $(value).parent().attr('title');
                      if (samplekind === undefined) {
                          samplekind = 'INTRO.SAMPLE_KIND.NAME:"' + value + '"';
                      } else {
                          samplekind = 'INTRO.SAMPLE_KIND.NAME:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets += " AND " + samplekind;
                      } else {
                          facets = " AND " + samplekind;
                      }
                  }
                  if (value.name == "authors") {
                      var value = $(value).parent().attr('title').split(" ");;
                      if (authors === undefined) {
                          authors = 'INTRO.FILE_CREATOR.NAME:"' + value[0] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[1] + '"';
                          if (value.length == 3) {
                              authors = 'INTRO.FILE_CREATOR.NAME:"' + value[0] + " " + value[1] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[2] + '"';
                          }
                      } else {
                          authors = ' INTRO.FILE_CREATOR.NAME:"' + value[0] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[1] + '"';
                          if (value.length == 3) {
                              authors = ' INTRO.FILE_CREATOR.NAME:"' + value[0] + " " + value[1] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[2] + '"';
                          }
                      }
                      if (facets !== undefined) {
                          facets += " AND " + authors;
                      } else {
                          facets = " AND " + authors;
                      } 
                  }
                  if (value.name == "keywords") {
                      var value = $(value).parent().attr('title');
                      if (keywords === undefined) {
                          keywords = 'INTRO.KEYWORDS.NAME:"' + value + '"';
                      } else {
                          keywords = '  INTRO.KEYWORDS.NAME:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + keywords;
                      } else {
                          facets = " AND " + keywords;
                      }
                  }
                  if (value.name == "scientific_field") {
                      var value = $(value).parent().attr('title');
                      if (scientific_field === undefined) {
                          scientific_field = 'INTRO.SCIENTIFIC_FIELD.NAME:"' + value + '"';
                      } else {
                          scientific_field = '  INTRO.SCIENTIFIC_FIELD.NAME:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + scientific_field;
                      } else {
                          facets = " AND " + scientific_field;
                      }
                  }
                  if (value.name == "language") {
                      var value = $(value).parent().attr('title');
                      if (language === undefined) {
                          language = 'INTRO.LANGUAGE:"' + value + '"';
                      } else {
                          language = ' INTRO.LANGUAGE:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + language;
                      } else {
                          facets = " AND " + language;
                      }
                  }
                  if (value.name == "filetype") {
                      var value = $(value).parent().attr('title');
                      if (filetype === undefined) {
                          filetype = 'DATA.FILES.FILETYPE:"' + value + '"';
                      } else {
                          filetype = ' DATA.FILES.FILETYPE:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + filetype;
                      } else {
                          facets = " AND " + filetype;
                      }
                  }
              })
              if (facets === undefined) {
                  facets = "";
              } else {
                  facets = facets
              }
              var query = APP.modules.search.$_GET('query');
              $('#results').empty();
              globalfacets = facets;
              APP.modules.search.search(query + facets, "facets", "0");
          },
          //Methode recherche 
          search: function(query, facets, current) {
              if (current != 0) {
                  if (current.text == 1) {
                      from = 0;
                  } else {

                      from = (current.text * 10) - 10;
                  }
              } else {
                  from = 0;
              }
              $.post("index.php/getinfo", {
                  query: query,
                  from: from
              }, function(data) {
                  data = JSON.parse(data)
                  if (!facets) {
                      APP.modules.search.AppendFacets(data);
                  }
                  APP.modules.datatable.AppendTable(data, facets, query, current, "search");
              })
          }
      }
  })()
  // Module mypublications, Affichage des publication de l'utilisateur courant sous forme de pagination
  APP.modules.mypublications = (function() {
      return {
          init: function() {
              var query = APP.modules.mypublications.$_GET('query');
              APP.modules.mypublications.search(query, null, "0");
          },
          AppendFacets: function(data) {
              if (data['aggregations'] == null) {} else {
                  $('#info').hide();
                  $('#facets').hide();
                  $('#results').empty();
                  $('#facets_type').empty();
                  sample_kind = data['aggregations']['sample_kind']['buckets'];
                  if (sample_kind.length == 0) {
                      $(".ui.card #samplekind").parent().hide();
                  }
                  $("#samplekind").append('<div class="header" >Sample kind</div>');
                  for (var k in sample_kind) {
                      count = sample_kind[k]['doc_count']
                      type = sample_kind[k]['key']
                      longtype = type
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#samplekind').append('<label title="' + longtype + '"  class="item" for="' + type + '"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="' + type + '" name="sample_kind" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  authors = data['aggregations']['authors']['buckets'];
                  if (authors.length == 0) {
                      $(".ui.card #authors").parent().hide();
                  }
                  $("#authors").append('<div class="header" >Authors</div>');
                  for (var k in authors) {
                      type = authors[k]['key']
                      count = authors[k]['doc_count']
                      longtype = type
                      if (type.length >= 25) {
                          type = type.substring(0, 25) + "...";
                      }
                      if (type == "" || type == " ") {} else {
                          $('#authors').append('<label title="' + longtype + '"  class="item" for="' + type + 'authors"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="' + type + 'authors" name="authors" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  keywords = data['aggregations']['keywords']['buckets'];
                  if (keywords.length == 0) {
                      $(".ui.card #keywords").parent().hide();
                  }
                  $("#keywords").append('<div class="header" >Keywords</div>');
                  for (var k in keywords) {
                      count = keywords[k]['doc_count']
                      type = keywords[k]['key']
                      longtype = type
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "") {} else {
                          $('#keywords').append('<label title="' + longtype + '"  class="item" for="' + type + 'keywords"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="' + type + 'keywords" name="keywords" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  scientific_field = data['aggregations']['scientific_field']['buckets'];
                  if (scientific_field.length == 0) {
                      $(".ui.card #scientificfields").parent().hide();
                  }
                  $("#scientificfield").append('<div class="header" >Scientific fields</div>');
                  for (var k in scientific_field) {
                      count = scientific_field[k]['doc_count']
                      type = scientific_field[k]['key']
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "") {} else {
                          $('#scientificfield').append('<label title="' + longtype + '"  class="item" for="' + type + 'scientificfield"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="' + type + 'scientificfield" name="scientific_field" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  language = data['aggregations']['language']['buckets'];
                  if (language.length == 0) {
                      $(".ui.card #language").parent().hide();
                  }
                  $("#language").append('<div class="header" >Languages</div>');
                  for (var k in language) {
                      count = language[k]['doc_count']
                      type = language[k]['key'];
                      longtype = type;
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "") {} else {
                          $('#language').append('<label title="' + longtype + '"  class="item" for="' + type + 'language"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="' + type + 'language" name="language" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  filetype = data['aggregations']['filetype']['buckets'];
                  if (filetype.length == 0) {
                      $(".ui.card #filetype").parent().hide();
                  }
                  $("#filetype").append('<div class="header" >Filetypes</div>');
                  for (var k in filetype) {
                      count = filetype[k]['doc_count']
                      type = filetype[k]['key'];
                      longtype = type
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "") {} else {
                          $('#filetype').append('<label title="' + longtype + '"  class="item" for="' + type + 'filetype"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="' + type + 'filetype" name="filetype" value="' + type + '" type="checkbox"> <div class="ui blue horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  accessright = data['aggregations']['access_right']['buckets'];
                  if (accessright.length == 0) {
                      $(".ui.card #accessright").parent().hide();
                  }
                  $("#accesright").append('<div class="header" >Access right</div>');
                  for (var k in accessright) {
                      count = accessright[k]['doc_count']
                      type = accessright[k]['key'];
                      longtype = type
                      if (type == "Open") {
                          color = "green";
                      }
                      if (type == "Closed") {
                          color = "red";
                      }
                      if (type == "Draft") {
                          color = "grey";
                      }
                      if (type == "Embargoed") {
                          color = "orange";
                      }
                      if (type == "Unpublished") {
                          color = "yellow";
                      }
                      if (type.length >= 25) {
                          type = type.substring(0, 20) + "...";
                      }
                      if (type == "") {} else {
                          $('#accesright').append('<label  class="item" for="' + type + 'accessright"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="' + type + 'accessright" name="accessright" value="' + type + '" type="checkbox"> <div class="ui ' + color + ' horizontal label">' + type + '</div>' + count + '</label>')
                      }
                  }
                  var creationdatearray = [];
                  aggs = data['aggregations'];
                  data = data['hits']['hits'];
                  $("#date").append('<div class="header" > Creation date</div>');
                  minyear = aggs['min_date']['value_as_string'].split("-", 2);
                  maxyear = aggs['max_date']['value_as_string'].split("-", 2);
                  minyear = minyear[0];
                  maxyear = maxyear[0];
                  if (minyear == maxyear) {
                      minyear = maxyear - 1;
                  }
                  $('#date').append('<input class="range-slider" value="' + minyear + ',' + maxyear + '" type="hidden">');
                  $('.range-slider').jRange({
                      from: minyear,
                      to: maxyear,
                      step: 1,
                      format: '%s',
                      width: 300,
                      showLabels: true,
                      isRange: true,
                      ondragend: function(val) {
                          var query = "*";
                          range = val.split(",", 2);
                          mindate = range[0];
                          maxdate = range[1];
                          date = " AND INTRO.CREATION_DATE:[" + mindate + "-01-01 TO " + maxdate + "-12-31]"
                          $('#results').empty();
                          if (typeof(globalfacets) != 'undefined') {
                              APP.modules.mypublications.search(query + globalfacets + date, "facets", "0");
                          } else {
                              APP.modules.mypublications.search(query + date, "facets", "0");
                          }
                      },
                      onbarclicked: function(val) {
                          var query = "*";
                          range = val.split(",", 2);
                          mindate = range[0];
                          maxdate = range[1];
                          date = " AND INTRO.CREATION_DATE:[" + mindate + "-01-01 TO " + maxdate + "-12-31]"
                          $('#results').empty();
                          if (typeof(globalfacets) != 'undefined') {
                              APP.modules.mypublications.search(query + globalfacets + date, "facets", "0");
                          } else {
                              APP.modules.mypublications.search(query + date, "facets", "0");
                          }
                      }
                  });
                  $('#facets').show();
                  $('form .ui.grid#gridmypublications').prepend('<div id="Displayfacets" class="ui button primary">Display/Hide facets</div>');
                  APP.modules.search.checksize();
                  $('form .ui.grid .button').on("click", function() {
                      $(".facets").toggle();
                  })
                  $(window).resize(function() {
                      APP.modules.search.checksize();

                  });

              }
          },
          checkCheckbox: function() {
              checked = $("#facets input:checked");
              var samplekind;
              var authors;
              var facets;
              var project_name;
              var scientific_field;
              var access_right;
              var filetype;
              var keywords;
              var language;
              facets = undefined;
              $.each(checked, function(index, value) {
                  if (value.name == "accessright") {
                      var value = value.value;
                      if (access_right === undefined) {
                          access_right = 'INTRO.ACCESS_RIGHT:"' + value + '"';
                      } else {
                          access_right = access_right + ' OR INTRO.ACCESS_RIGHT:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets.replace(/ AND \([^)]*\) */g, "");
                          facets += " AND " + "(" + access_right + ")";
                      } else {
                          facets = ' AND ' + "(" + access_right + ")";
                      }
                  }
                  if (value.name == "sample_kind") {
                      var value = $(value).parent().attr('title');
                      if (samplekind === undefined) {
                          samplekind = 'INTRO.SAMPLE_KIND.NAME:"' + value + '"';
                      } else {
                          samplekind = ' INTRO.SAMPLE_KIND.NAME:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets += " AND " + samplekind;
                      } else {
                          facets = ' AND ' + samplekind;
                      }
                  }
                  if (value.name == "authors") {
                      var value = $(value).parent().attr('title').split(" ");
                      if (authors === undefined) {
                          authors = ' INTRO.FILE_CREATOR.NAME:"' + value[0] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[1] + '"';
                          if (value.length == 3) {
                              authors = ' INTRO.FILE_CREATOR.NAME:"' + value[0] + " " + value[1] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[2] + '"';
                          }
                      } else {
                          authors = '  INTRO.FILE_CREATOR.NAME:"' + value[0] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[1] + '"';
                          if (value.length == 3) {
                              authors = '  INTRO.FILE_CREATOR.NAME:"' + value[0] + " " + value[1] + '" AND INTRO.FILE_CREATOR.FIRST_NAME:"' + value[2] + '"';
                          }
                      }
                      if (facets !== undefined) {
                          facets += " " + authors;
                      } else {
                          facets = ' AND ' + authors;
                      } 
                  }
                  if (value.name == "keywords") {
                      var value = $(value).parent().attr('title');
                      if (keywords === undefined) {
                          keywords = 'INTRO.KEYWORDS.NAME:"' + value + '"';
                      } else {
                          keywords = '  INTRO.KEYWORDS.NAME:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + keywords;
                      } else {
                          facets = ' AND ' + keywords;
                      }
                  }
                  if (value.name == "scientific_field") {
                      var value = $(value).parent().attr('title');
                      if (scientific_field === undefined) {
                          scientific_field = 'INTRO.SCIENTIFIC_FIELD.NAME:"' + value + '"';
                      } else {
                          scientific_field = '  INTRO.SCIENTIFIC_FIELD.NAME:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + scientific_field;
                      } else {
                          facets = ' AND ' + scientific_field;
                      }
                  }
                  if (value.name == "language") {
                      var value = $(value).parent().attr('title');
                      if (language === undefined) {
                          language = 'INTRO.LANGUAGE:"' + value + '"';
                      } else {
                          language = '  INTRO.LANGUAGE:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + language;
                      } else {
                          facets = ' AND ' + language;
                      }
                  }
                  if (value.name == "filetype") {
                      var value = $(value).parent().attr('title');
                      if (filetype === undefined) {
                          filetype = 'DATA.FILES.FILETYPE:"' + value + '"';
                      } else {
                          filetype = '  DATA.FILES.FILETYPE:"' + value + '"';
                      }
                      if (facets !== undefined) {
                          facets = facets + " AND " + filetype;
                      } else {
                          facets = ' AND ' + filetype;
                      }
                  }
              })
              if (facets === undefined) {
                  facets = "";
              } else {
                  facets = facets
              }
              $('#results').empty();
              globalfacets = facets;
              APP.modules.mypublications.search("*" + facets, 'facets', "0");
          },
          $_GET: function(param) {
              var vars = {};
              window.location.href.replace(location.hash, '').replace(/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
                  function(m, key, value) { // callback
                      vars[key] = value !== undefined ? value : '';
                  });
              if (param) {
                  return vars[param] ? vars[param] : null;
              }
              return vars;
          },
          search: function(query, facets, current) {
              if (current != 0) {
                  if (current.text == 1) {
                      from = 0;
                  } else {

                      from = (current.text * 10) - 10;
                  }
              } else {
                  from = 0;
              }
              $.post("index.php/getmypublications", {
                  query: query,
                  from: from
              }, function(data) {
                  data = JSON.parse(data);
                  if (!facets) {
                      APP.modules.mypublications.AppendFacets(data);
                  }
                  APP.modules.datatable.AppendTable(data, facets, query, current, "mypublications");
              })
          }
      }
  })()
  // Module upload, permet de controller le formulaire upload et edit
  APP.modules.upload = (function() {
      return {
          checkform: function() {
              $('.ui .form.dataset').form({
                  keyboardShortcuts: false,
                  fields: {
                      title: {
                          identifier: 'title',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter title'
                          }]
                      },
                      authors_firstname: {
                          identifier: 'authors_firstname',
                          rules: [{
                              type: 'regExp[^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ -.,]*$]',
                              prompt: 'Please enter a valid firstname'
                          }]
                      },
                      authors_name: {
                          identifier: 'authors_name',
                          rules: [{
                              type: 'regExp[^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ -.,]*$]',
                              prompt: 'Please enter a valid name'
                          }]
                      },
                      authors_email: {
                          identifier: 'authors_email',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter authors email'
                          }]
                      },
                      description: {
                          identifier: 'description',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter description'
                          }]
                      },
                      scientific_field: {
                          identifier: 'scientific_field',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter a scientific field'
                          }]
                      },
                      measurement_nature: {
                          identifier: 'measurement_nature',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter measurement nature'
                          }]
                      },
                      measurement_abbreviation: {
                          identifier: 'measurement_abbreviation',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter measurement abbreviation'
                          }]
                      },
                      measurement_unit: {
                          identifier: 'measurement_unit',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter measurement unit'
                          }]
                      },
                      license: {
                          identifier: 'license',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter license'
                          }]
                      },
                      selectaccessright: {
                          identifier: 'access_right',
                          rules: [{
                              type: 'checked',
                              prompt: 'Please select accessright'
                          }]
                      },
                      institutions: {
                          identifier: 'institution',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter institution'
                          }]
                      },
                      publisher: {
                          identifier: 'publisher',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please enter a publisher'
                          }]
                      },
                      file: {
                          identifier: 'file',
                          rules: [{
                              type: 'empty',
                              prompt: 'Please select a file'
                          }]
                      },

                  }
              });

          },
          init: function() {
              $('form ').on('keypress', function(e) {
                  return e.which !== 13;
              });
              $(".save").click(function() {
                  $('.ui .form.dataset').form({
                      keyboardShortcuts: false,
                      fields: {
                          title: {
                              identifier: 'title',
                              rules: [{
                                  type: 'empty',
                                  prompt: 'Please enter title'
                              }]
                          }
                      }
                  });
              });
              $(".publish").click(function() {
                  APP.modules.upload.checkform();
              });
              $('.ui.accordion').accordion();
              APP.modules.upload.checkform();
              $("#sampling_points input[name='sampling_point_longitude[]']").on('keypress', function(e) {
                  return e.metaKey || // cmd/ctrl
                      e.which <= 0 || // arrow keys
                      e.which == 8 || // delete key
                      /[0-9,-.]/.test(String.fromCharCode(e.which)); // numbers
              })
              $("#sampling_points input[name='sampling_point_latitude[]'] ").on('keypress', function(e) {
                  return e.metaKey || // cmd/ctrl
                      e.which <= 0 || // arrow keys
                      e.which == 8 || // delete key
                      /[0-9,-.]/.test(String.fromCharCode(e.which)); // numbers
              })
              $('#sampling_points input')
                  .popup({
                      on: 'focus'
                  });
              $("#addauthors").click(function(e) {
                  $("#authors").append('<div class="required field" > <div class="three fields"> <div class="field"><label>Author firstname</label><input type="text" name="authors_firstname[]"   placeholder="First Name" required></div> <div class="field"><label>Author name</label><input type="text" name="authors_name[]" placeholder="Family Name, Given names" required></div> <div class="field"><label>Author mail</label><input type="email" name="authors_email[]" placeholder="Email" required ></div> <div class="ui icon delete center"><i class="remove icon"></i></div> </div> </div>');
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addfile").click(function(e) {
                  $("#files").append('<div class="row"></div><div class="ui input"><input data-validate="file" type="file" name="file[]" required><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              var i = 0;
              $("#addkeywords").click(function(e) {
                  i = $('#keywords input').length;
                  if (i <= 2) {
                      $("#keywords").append('<div class="ui input"><input type="text" name="keywords[]"" placeholder="Keyword"><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                  }
              });
              var i = 0;
              $("#addacronym").click(function(e) {
                  i = $('#acronym .fields').length;
                  if (i <= 9) {
                      $("#acronym").append('<div class=" fields"> <div class="field"><input type="text" name="acronym_abbreviation[]" placeholder="Acronym" ></div> <div class="field"><input type="text" name="acronym_description[]" placeholder="Description" ></div> <div class="ui icon delete "><i class="remove icon"></i></div> </div>');
                  }
              });
              var i = 0;
              $("#addmethodology").click(function(e) {
                  i = $('#methodology .fields').length;
                  if (i <= 9) {
                      $("#methodology").append('<div class=" fields"> <div class="field"><input type="text"   placeholder="Keywords" name="methodology_name[]" ></div> <div class="field"><input type="text" name="methodology_description[]" placeholder="Description" ></div> <div class="ui icon delete "><i class="remove icon"></i></div> </div>');
                  }
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addfundings").click(function(e) {
                  i = $('#fundings input').length;
                  if (i <= 2) {
                      $("#fundings").append('<div class="ui input"><input type="text" name="fundings[]"" placeholder="Funding"><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                  }
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addinstitution").click(function(e) {
                  $("#institution").append('<div class="ui input"><div class="ui dropdown fluid search selection optgroup institution" "> <input type="hidden" name="institution[]"> <div class="default text">Select a field or type one</div> <i class="dropdown icon"></i> <div class="menu"> <div class="item">ADIT Agency for the dissemination of technological information</div> <div class="item">ANDRA National Agency for Radioactive Waste Management</div> <div class="item">B.R.G.M. THE FRENCH GEOLOGICAL SURVEY</div> <div class="item">C.E.A. The French Alternative Energies and Atomic Energy Commission</div> <div class="item">C.E.E. Center for Employment Studies</div> <div class="item">CEPH Human plymorphism Study Center</div> <div class="item">CIRAD Agricultural Research for development</div> <div class="item">Cité des sciences et Palais de la découverte</div> <div class="item">National Museum of the History of Immigration</div> <div class="item">CNES National Center for Space Studies</div> <div class="item">C.N.R.S. National Center for Scientific Research</div> <div class="item">GENOPOLE France’s leading biocluster for biotechnologies and research in genomics and genetics</div> <div class="item">IFE French Institute of Education</div> <div class="item">IFREMER French Research Institute for Exploitation of the Sea</div> <div class="item">IFPEN Research and training player in the ﬁelds of energy, transport and the environment</div> <div class="item">IFSTTAR French Institute of science and technology for transport, development and networks</div> <div class="item">INCA National Institute of cancer</div> <div class="item">INED National Institute for Demographic Studies</div> <div class="item">INERIS National Institute for the Industrial Environment and Risks</div> <div class="item">INRA National Institute of Agronomic Research</div> <div class="item">INRIA National Institute for Research in Computer Science and Automation</div> <div class="item">INSERM National Institute of Health and Medical Research</div> <div class="item">Curie Institute</div> <div class="item">Pasteur Institute</div> <div class="item">IPEV French Polar Institute Paul Emile Victor</div> <div class="item">I.R.D. Research Institute for Development</div> <div class="item">I.R.S.N. Institute for Radiation Protection and Nuclear Safety</div> <div class="item">IRSTEA National Science and Technology Research Institute for Environment and Agriculture</div> <div class="item">M.N.H.N National Museum of Natural History</div> <div class="item">Quai Branly Museum</div> <div class="item">ONERA National Office for Aeronautical Studies and Research</div> <div class="item">OSEO</div> <div class="item">O.S.T. Science and Technology Observatory</div> <div class="item">RENATER National Telecommunication Network for Technology, Education and Research</div></div> </div><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                  $('.ui .dropdown.institution').dropdown({
                      allowAdditions: true
                  });
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addsampling_point").click(function(e) {
                  $("#sampling_points").append('<div> <div class="field"><label>Name</label> <input type="text" name="sampling_point_name[]" placeholder="Name" ></div> <div class="field"> <label>Coordinate reference system</label> <div class="ui input"> <div class="ui dropdown fluid search selection optgroup coordinate"> <input  type="hidden" name="sampling_point_coordinate_system[]"> <div class="default text">Select a coordinate system or type one</div> <i class="dropdown icon"></i> <div class="menu"><div class="item">NTF meridian of Paris</div><div class="item">WGS84 Greenwich</div> <div class="item">ED50 Greenwich</div> <div class="item">NTF Lambert 1</div> <div class="item">NTF Lambert 2 extend</div> <div class="item">ED50 UTM 32</div> <div class="item">WGS84 UTM 32</div> <div class="item">RGF93 projection Lambert93</div> </div> </div> </div> </div> <div class="field"><label>Abbreviation</label><input type="text"  name="sampling_point_abbreviation[]" placeholder="abbreviation"></div> <div class="field"><label>Longitude</label><input type="number" data-content="Type a longitude in decimal format" step="any" value="{{sampling_point.LONGITUDE}}" name="sampling_point_longitude[]" placeholder="longitude" ></div> <div class="field"><label>Latitude</label><input type="number" data-content="Type a latitude in decimal format" step="any" value="{{sampling_point.LATITUDE}}" name="sampling_point_latitude[]" placeholder="latitude"></div> <div class="field"><label>Elevation</label><input type="number"  name="sampling_point_elevation[]" placeholder="elevation"></div> <div class="field"><label>Additionnal description</label><textarea name="sampling_point_description[]" placeholder="Additionnal description"></textarea></div> <div id="deletesamplingpoint" class="ui icon delete"><i class="remove icon"></i></div></div>');
                  $('.ui .dropdown.coordinate').dropdown({
                      allowAdditions: true
                  });
                  $('#sampling_points input')
                      .popup({
                          on: 'focus'
                      });
                  $("#sampling_points input[name='sampling_point_longitude[]']").on('keypress', function(e) {
                      return e.metaKey || // cmd/ctrl
                          e.which <= 0 || // arrow keys
                          e.which == 8 || // delete key
                          /[0-9,-.]/.test(String.fromCharCode(e.which)); // numbers
                  })
                  $("#sampling_points input[name='sampling_point_latitude[]'] ").on('keypress', function(e) {
                      return e.metaKey || // cmd/ctrl
                          e.which <= 0 || // arrow keys
                          e.which == 8 || // delete key
                          /[0-9,-.]/.test(String.fromCharCode(e.which)); // numbers
                  })
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addsamplingdate").click(function(e) {
                  $("#sampling_date").append('        <div class="ui input"><input type="date"  class="date" name="sampling_date[]" placeholder="Sampling date" ><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                  $(".date").datepicker({
                      dateFormat: "yy-mm-dd"
                  });
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addsamplekind").click(function(e) {
                  $("#sample_kind").append('<div class="ui input"><input type="text" name="sample_kind[]" placeholder="Sample kind" ><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addmeasurement").click(function(e) {
                  $("#measurements").append('<div class="three fields"> <div class="field"><label>Measurement nature</label><input type="text"  name="measurement_nature[]"  placeholder="Nature" ></div> <div class="field"><label>Measurement abbreviation</label><input type="text"  name="measurement_abbreviation[]" data-validate="measurement_abbreviation" placeholder="Abbreviation" ></div> <div class="field"><label>Measurement unit(s)</label><input type="text"  name="measurement_unit[]" data-validate="measurement_unit" id="units" placeholder="Unit(s)" ></div> <input type="checkbox" class="checker" /><label for="checker">No units</label><div class="ui icon delete center"><i class="remove icon"></i></div></div>');
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $("#addscientificfields").click(function(e) {
                  i = $('#scientificfields select').length;
                  if (i <= 2) {
                      $("#scientificfields").append('<div class="ui input"><select class="ui fluid search dropdown scientific_field" name="scientific_field[]" > <option value="">Select a field or type one</option> <option value="Addresses">Addresses</option> <option value="Hydrography"> Hydrography</option> <option value="Administrative units"> Administrative units</option> <option value="Land cover"> Land cover</option> <option value="Agricultural and aquaculture facilities"> Agricultural and aquaculture facilities</option> <option value="Land use"> Land use</option> <option value="Area management/restriction/regulation zones and reporting units"> Area management/restriction/regulation zones and reporting units</option> <option value="Meteorological geographical features"> Meteorological geographical features</option> <option value="Atmospheric conditions"> Atmospheric conditions</option> <option value="Mineral resources"> Mineral resources</option> <option value="Bio-geographical regions"> Bio-geographical regions</option> <option value="Natural risk zones"> Natural risk zones</option> <option value="Buildings"> Buildings</option> <option value="Oceanographic geographical features"> Oceanographic geographical features</option> <option value="Cadastral parcels"> Cadastral parcels</option> <option value="Orthoimagery"> Orthoimagery</option> <option value="Coordinate reference systems">Coordinate reference systems</option> <option value="Population distribution — demography">Population distribution — demography</option> <option value="Elevation">Elevation</option> <option value="Production and industrial facilities">Production and industrial facilities</option> <option value="Energy resources">Energy resources</option> <option value="Protected sites">Protected sites</option> <option value="Environmental monitoring facilities">Environmental monitoring facilities</option> <option value="Sea regions">Sea regions</option> <option value="Geographical grid systems">Geographical grid systems</option> <option value="Soil">Soil</option> <option value="Geographical names">Geographical names</option> <option value="Species distribution">Species distribution</option> <option value="Geology">Geology</option> <option value="Statistical units">Statistical units</option> <option value="Habitats and biotopes">Habitats and biotopes</option> <option value="Transport networks">Transport networks</option> <option value="Human health and safety">Human health and safety</option> <option value="Utility and governmental services">Utility and governmental services</option> </select><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                      $('.ui .dropdown.scientific_field').dropdown({
                          allowAdditions: true
                      });
                  }
              });
              $("body").on("click", ".delete", function(e) {
                  $(this).parent("div").remove();
              });
              $('body').on('click', ".checker", function() {
                  if ($(this).is(':checked')) {
                      $(this).parent("div").children().find("#units").prop('readonly', true);
                      $(this).parent("div").children().find("#units").val('No units');
                  } else {
                      $(this).parent("div").children().find("#units").prop('readonly', false);
                      $(this).parent("div").children().find("#units").val('');
                  }
              });
              $('body').on('click', ".empty_measurement", function() {
                  if ($(this).is(':checked')) {
                      $($(this).parent("div").children().find(":input")[0]).prop('readonly', true);
                      $($(this).parent("div").children().find(":input")[0]).val('No nature');
                      $($(this).parent("div").children().find(":input")[1]).prop('readonly', true);
                      $($(this).parent("div").children().find(":input")[1]).val('No abbreviation');
                      $($(this).parent("div").children().find(":input")[2]).prop('readonly', true);
                      $($(this).parent("div").children().find(":input")[2]).val('No units');
                  } else {
                      $($(this).parent("div").children().find(":input")[0]).prop('readonly', false);
                      $($(this).parent("div").children().find(":input")[0]).val('');;
                      $($(this).parent("div").children().find(":input")[1]).prop('readonly', false);
                      $($(this).parent("div").children().find(":input")[1]).val('');
                      $($(this).parent("div").children().find(":input")[2]).prop('readonly', false);
                      $($(this).parent("div").children().find(":input")[2]).val('');
                  }
              });
            function handleFileSelect(evt) {
                var files = evt.target.files; // FileList object
                var file = files[0];
               var reader = new FileReader();
                  reader.readAsText(file);
              $('form')[1].reset();
              $('.delete').parent().remove();
                  reader.onload = function(event){
                    var csv = event.target.result;
                    var data = $.csv.toArrays(csv);
                             name="";
                             firstname="";
                             mail="";
                  $.each(data, function(index, values) {
                          $.each(values, function(index, value) {
                            if (value!="") {
                              if (value=="TITLE") {
                                $("input[name='title']").val(values[1]);
                              }
                               if (value=="LANGUAGE") {
                                if (values[1].toLowerCase()=="english" ) {
                                  language="1";
                                }
                                if (values[1].toLowerCase()=="french") {
                                  language="2";
                                }
                                $("select[name='language']").val(language);
                              }
                               if (value=="NAME") {
                                name=values[1];
                               
                              }
                               if (value=="FIRST NAME") {
                                firstname=values[1];

                              }
                               if (value=="MAIL") {
                                mail=values[1];
                               if ($("input[name='authors_email[]']").val()!=mail) {

                              $("#authors").append('<div class="required field" > <div class="three fields"> <div class="field"><label>Author firstname</label><input type="text" name="authors_firstname[]" value="'+firstname+'"  placeholder="First Name" required></div> <div class="field"><label>Author name</label><input type="text" name="authors_name[]" value="'+name+'" placeholder="Family Name, Given names" required></div> <div class="field"><label>Author mail</label><input type="email" name="authors_email[]" value="'+mail+'" placeholder="Email" required ></div> <div class="ui icon delete center"><i class="remove icon"></i></div> </div> </div>');
                               } 

                              }
                                if (value=="KEYWORD") {
                                  i = $('#keywords input').length;
                                    if (i <= 2) {

                                  if ($("input[name='keywords[]']").val()!="") {
                                    $("#keywords").append('<div class="ui input"><input type="text"  value="'+values[1]+'" name="keywords[]"" placeholder="Keyword"><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                                  }
                                  else{
                                   $("input[name='keywords[]']").val(values[1]);
                                  }
                                }

                              }
                               if (value=="SCIENTIFIC FIELD") {
                                  i = $('#scientificfields input').length;
                                    if (i <= 2) {

                                  if ($("select[name='scientific_field[]']").val()!="") {
                                  $("#scientificfields").append('<div class="ui input"><select class="ui fluid search dropdown scientific_field" name="scientific_field[]" > <option value="'+values[1]+'">'+values[1]+'</option> <option value="Addresses">Addresses</option> <option value="Hydrography"> Hydrography</option> <option value="Administrative units"> Administrative units</option> <option value="Land cover"> Land cover</option> <option value="Agricultural and aquaculture facilities"> Agricultural and aquaculture facilities</option> <option value="Land use"> Land use</option> <option value="Area management/restriction/regulation zones and reporting units"> Area management/restriction/regulation zones and reporting units</option> <option value="Meteorological geographical features"> Meteorological geographical features</option> <option value="Atmospheric conditions"> Atmospheric conditions</option> <option value="Mineral resources"> Mineral resources</option> <option value="Bio-geographical regions"> Bio-geographical regions</option> <option value="Natural risk zones"> Natural risk zones</option> <option value="Buildings"> Buildings</option> <option value="Oceanographic geographical features"> Oceanographic geographical features</option> <option value="Cadastral parcels"> Cadastral parcels</option> <option value="Orthoimagery"> Orthoimagery</option> <option value="Coordinate reference systems">Coordinate reference systems</option> <option value="Population distribution — demography">Population distribution — demography</option> <option value="Elevation">Elevation</option> <option value="Production and industrial facilities">Production and industrial facilities</option> <option value="Energy resources">Energy resources</option> <option value="Protected sites">Protected sites</option> <option value="Environmental monitoring facilities">Environmental monitoring facilities</option> <option value="Sea regions">Sea regions</option> <option value="Geographical grid systems">Geographical grid systems</option> <option value="Soil">Soil</option> <option value="Geographical names">Geographical names</option> <option value="Species distribution">Species distribution</option> <option value="Geology">Geology</option> <option value="Statistical units">Statistical units</option> <option value="Habitats and biotopes">Habitats and biotopes</option> <option value="Transport networks">Transport networks</option> <option value="Human health and safety">Human health and safety</option> <option value="Utility and governmental services">Utility and governmental services</option> </select><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                                        $('.ui .dropdown.scientific_field').dropdown({
                                            allowAdditions: true
                                        });                                  }
                                  else{
                                    $("#scientificfields .input").remove();
                                  $("#scientificfields").append('<div class="ui input"><select class="ui fluid search dropdown scientific_field" name="scientific_field[]" > <option value="'+values[1]+'">'+values[1]+'</option> <option value="Addresses">Addresses</option> <option value="Hydrography"> Hydrography</option> <option value="Administrative units"> Administrative units</option> <option value="Land cover"> Land cover</option> <option value="Agricultural and aquaculture facilities"> Agricultural and aquaculture facilities</option> <option value="Land use"> Land use</option> <option value="Area management/restriction/regulation zones and reporting units"> Area management/restriction/regulation zones and reporting units</option> <option value="Meteorological geographical features"> Meteorological geographical features</option> <option value="Atmospheric conditions"> Atmospheric conditions</option> <option value="Mineral resources"> Mineral resources</option> <option value="Bio-geographical regions"> Bio-geographical regions</option> <option value="Natural risk zones"> Natural risk zones</option> <option value="Buildings"> Buildings</option> <option value="Oceanographic geographical features"> Oceanographic geographical features</option> <option value="Cadastral parcels"> Cadastral parcels</option> <option value="Orthoimagery"> Orthoimagery</option> <option value="Coordinate reference systems">Coordinate reference systems</option> <option value="Population distribution — demography">Population distribution — demography</option> <option value="Elevation">Elevation</option> <option value="Production and industrial facilities">Production and industrial facilities</option> <option value="Energy resources">Energy resources</option> <option value="Protected sites">Protected sites</option> <option value="Environmental monitoring facilities">Environmental monitoring facilities</option> <option value="Sea regions">Sea regions</option> <option value="Geographical grid systems">Geographical grid systems</option> <option value="Soil">Soil</option> <option value="Geographical names">Geographical names</option> <option value="Species distribution">Species distribution</option> <option value="Geology">Geology</option> <option value="Statistical units">Statistical units</option> <option value="Habitats and biotopes">Habitats and biotopes</option> <option value="Transport networks">Transport networks</option> <option value="Human health and safety">Human health and safety</option> <option value="Utility and governmental services">Utility and governmental services</option> </select> </div>');
                                        $('.ui .dropdown.scientific_field').dropdown({
                                            allowAdditions: true
                                        });   
                                  }
                                }

                              }
                                if (value=="FUNDING") {
                                  i = $('#fundings input').length;
                                    if (i <= 2) {


                                  if ($("input[name='fundings[]']").val()!="") {
                                      $("#fundings").append('<div class="ui input"><input type="text" value="'+values[1]+'" name="fundings[]"" placeholder="Funding"><div class="ui icon delete"><i class="remove icon"></i></div> </div>');

                                          }
                                  else{
                                   $("input[name='fundings[]']").val(values[1]);
                                  }
                                }

                              }
                              if (value=="INSTITUTION") {
                                   

                                  if ($("input[name='institution[]']").val()!="") {
                                    $("#institution").append('<div class="ui input"><div class="ui dropdown fluid search selection optgroup institution" "> <input type="hidden" value="'+values[1]+'" name="institution[]"> <div class="text">'+values[1]+'</div> <i class="dropdown icon"></i> <div class="menu"> <div class="item">ADIT Agency for the dissemination of technological information</div> <div class="item">ANDRA National Agency for Radioactive Waste Management</div> <div class="item">B.R.G.M. THE FRENCH GEOLOGICAL SURVEY</div> <div class="item">C.E.A. The French Alternative Energies and Atomic Energy Commission</div> <div class="item">C.E.E. Center for Employment Studies</div> <div class="item">CEPH Human plymorphism Study Center</div> <div class="item">CIRAD Agricultural Research for development</div> <div class="item">Cité des sciences et Palais de la découverte</div> <div class="item">National Museum of the History of Immigration</div> <div class="item">CNES National Center for Space Studies</div> <div class="item">C.N.R.S. National Center for Scientific Research</div> <div class="item">GENOPOLE France’s leading biocluster for biotechnologies and research in genomics and genetics</div> <div class="item">IFE French Institute of Education</div> <div class="item">IFREMER French Research Institute for Exploitation of the Sea</div> <div class="item">IFPEN Research and training player in the ﬁelds of energy, transport and the environment</div> <div class="item">IFSTTAR French Institute of science and technology for transport, development and networks</div> <div class="item">INCA National Institute of cancer</div> <div class="item">INED National Institute for Demographic Studies</div> <div class="item">INERIS National Institute for the Industrial Environment and Risks</div> <div class="item">INRA National Institute of Agronomic Research</div> <div class="item">INRIA National Institute for Research in Computer Science and Automation</div> <div class="item">INSERM National Institute of Health and Medical Research</div> <div class="item">Curie Institute</div> <div class="item">Pasteur Institute</div> <div class="item">IPEV French Polar Institute Paul Emile Victor</div> <div class="item">I.R.D. Research Institute for Development</div> <div class="item">I.R.S.N. Institute for Radiation Protection and Nuclear Safety</div> <div class="item">IRSTEA National Science and Technology Research Institute for Environment and Agriculture</div> <div class="item">M.N.H.N National Museum of Natural History</div> <div class="item">Quai Branly Museum</div> <div class="item">ONERA National Office for Aeronautical Studies and Research</div> <div class="item">OSEO</div> <div class="item">O.S.T. Science and Technology Observatory</div> <div class="item">RENATER National Telecommunication Network for Technology, Education and Research</div></div> </div><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                                    $('.ui .dropdown.institution').dropdown({
                                        allowAdditions: true
                                    });
                                  }                                  
                                  else{
                                   $("input[name='institution[]']").val(values[1]);
                                  }
                                

                              }
                              if (value=="DATA DESCRIPTION") {
                                $("textarea[name='description']").val(values[1]);
                              }
                                if (value=="METHODOLOGY") {
                                  i = $('#methodology .fields').length;
                                  if (i <= 9) {
                                      $("#methodology").append('<div class=" fields"> <div class="field"><input type="text"  value="'+values[1]+'"  placeholder="Keywords" name="methodology_name[]" ></div> <div class="field"><input type="text"  value="'+values[2]+'" name="methodology_description[]" placeholder="Description" ></div> <div class="ui icon delete "><i class="remove icon"></i></div> </div>');
                                  }
                              }
                               if (value=="ACRONYM") {
                                  i = $('#acronym_abbreviation_ .fields').length;
                                  if (i <= 9) {
                                      $("#acronym").append('<div class=" fields"> <div class="field"><input type="text" value="'+values[1]+'" name="acronym_abbreviation[]" placeholder="Acronym" ></div> <div class="field"><input type="text" value="'+values[2]+'" name="acronym_description[]" placeholder="Description" ></div> <div class="ui icon delete "><i class="remove icon"></i></div> </div>');
                                  }
                              }
                              if (value=="MEASUREMENT") {
                                 if ($("input[name='measurement_nature[]']").val()!="") {
                                $("#measurements").append('<div class="three fields"> <div class="field"><label>Measurement nature</label><input type="text" value="'+values[1]+'" name="measurement_nature[]"  placeholder="Nature" ></div> <div class="field"><label>Measurement abbreviation</label><input type="text"  value="'+values[2]+'" name="measurement_abbreviation[]" data-validate="measurement_abbreviation" placeholder="Abbreviation" ></div> <div class="field"><label>Measurement unit(s)</label><input type="text"   value="'+values[3]+'" name="measurement_unit[]" data-validate="measurement_unit" id="units" placeholder="Unit(s)" ></div> <input type="checkbox" class="checker" /><label for="checker">No units</label><div class="ui icon delete center"><i class="remove icon"></i></div></div>');
                                }
                                else{
                                    $("input[name='measurement_nature[]']").val(values[1]);
                                    $("input[name='measurement_abbreviation[]']").val(values[2]);
                                    $("input[name='measurement_unit[]']").val(values[3]);
                                }
                              }
                               if (value=="SAMPLE KIND") {
                                if ($("input[name='sample_kind[]']").val()!="") {
                                                    $("#sample_kind").append('<div class="ui input"><input type="text" value="'+values[1]+'" name="sample_kind[]" placeholder="Sample kind" ><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                                }
                                else{
                                $("input[name='sample_kind[]']").val(values[1]);
                                  
                                }

                              }
                              if (value=="SAMPLING DATE") {
                                if ($("input[name='sampling_date[]']").val()!="") {
                                   $("#sampling_date").append('        <div class="ui input"><input type="date"  class="date" value="'+values[1]+'" name="sampling_date[]" placeholder="Sampling date" ><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
                                                    $(".date").datepicker({
                                                        dateFormat: "yy-mm-dd"
                                                    });                                }
                                                                  else{
                                $("input[name='sampling_date[]']").val(values[1]);
                                  
                                }

                              }
                              if (value=="SAMPLING_POINT") {
                               $("#sampling_points").append('<div> <div class="field"><label>Name</label> <input type="text" name="sampling_point_name[]" value="'+values[1]+'" placeholder="Name" ></div> <div class="field"> <label>Coordinate reference system</label> <div class="ui input"> <div class="ui dropdown fluid search selection optgroup coordinate"> <input  type="hidden"  value="'+values[2]+'" name="sampling_point_coordinate_system[]"> <div class=" text">'+values[2]+'</div> <i class="dropdown icon"></i> <div class="menu"><div class="item">NTF meridian of Paris</div><div class="item">WGS84 Greenwich</div> <div class="item">ED50 Greenwich</div> <div class="item">NTF Lambert 1</div> <div class="item">NTF Lambert 2 extend</div> <div class="item">ED50 UTM 32</div> <div class="item">WGS84 UTM 32</div> <div class="item">RGF93 projection Lambert93</div> </div> </div> </div> </div> <div class="field"><label>Abbreviation</label><input type="text"  name="sampling_point_abbreviation[]"  value="'+values[3]+'" placeholder="abbreviation"></div> <div class="field"><label>Longitude</label><input type="text"   value="'+values[4]+'" name="sampling_point_longitude[]" placeholder="longitude" ></div> <div class="field"><label>Latitude</label><input type="text" data-content="Type a latitude in decimal format" step="any"  value="'+values[5]+'" name="sampling_point_latitude[]" placeholder="latitude"></div> <div class="field"><label>Elevation</label><input type="text"  name="sampling_point_elevation[]" value="'+values[6]+'" placeholder="elevation"></div> <div class="field"><label>Additionnal description</label><textarea name="sampling_point_description[]"   placeholder="Additionnal description">'+values[7]+'</textarea></div> <div id="deletesamplingpoint" class="ui icon delete"><i class="remove icon"></i></div></div>');
                  $('.ui .dropdown.coordinate').dropdown({
                      allowAdditions: true
                  });
                  $('#sampling_points input')
                      .popup({
                          on: 'focus'
                      });
                  $("#sampling_points input[name='sampling_point_longitude[]']").on('keypress', function(e) {
                      return e.metaKey || // cmd/ctrl
                          e.which <= 0 || // arrow keys
                          e.which == 8 || // delete key
                          /[0-9,-.]/.test(String.fromCharCode(e.which)); // numbers
                  })
                  $("#sampling_points input[name='sampling_point_latitude[]'] ").on('keypress', function(e) {
                      return e.metaKey || // cmd/ctrl
                          e.which <= 0 || // arrow keys
                          e.which == 8 || // delete key
                          /[0-9,-.]/.test(String.fromCharCode(e.which)); // numbers
                  })
                                                                 

                              }
                            }

                       });
                  });


  }
}

  document.getElementById('metadatafile').addEventListener('change', handleFileSelect, false);   




          },
          checkformbutton: function() {
              if (document.getElementById('embargoed').checked) {
                  document.getElementById('date_end').style.display = 'block';
                  document.getElementById('date_end').required = true;
              } else document.getElementById('date_end').style.display = 'none';
          }
      }
  })()
  // Module preview, Affichage d'un modal permettant de visualiser un fichier
  APP.modules.preview = (function() {
      return {
          previewdocument: function(link) {
              $("#preview").empty();
              $("#preview").append('<iframe src="' + link + '" style="width:100%; height:550px;" frameborder="0"></iframe>');
              $('.ui.modal.preview').modal('show');
          }
      }
  })()


  APP.modules.changelog = (function() {
      return {
          previewchangelog: function(link) {
              $("#changelog .grid .column").empty();
              $.getJSON(link, function(data) {
                  if (data[0] == 'Empty changelog file') {
                      $("#changelog .grid .column ").append('Metadata version is the first one!');

                  }
                  append=''
                  html = ''
                  $.each(data, function(index, value) {
                      version = '<div class=" title">Version '+value['version']+'</div><div class="content"><div class="ui green label right floated "  data-tooltip="Date of metadata">' + value['date'] + '</div><div class="ui green label right floated "   data-tooltip="User who submit modification">' + value['mailuser'] +"</div><div class='row'></div><div class='row'></div>";
                      $.each(value, function(name, value) {
                    text=""
                                             // console.log(value)
                      if (value['Type']=='modified') {
                        text += '<div class="ui raised segment"> <a class="ui red ribbon label">Old data</a> <span>' + value['OldValue'] + '</span> <p></p> <a class="ui blue ribbon label">New data</a> '+ value['NewValue'] + ' <p></p> </div>';
                        html += '<div class=" ui horizontal divider header"></div><div class="row"></div><a class="ui  label">' + name +'</a> <div class="ui raised segment"> ' + text + '</div>';
                      }
                      else if (value['Type']=='added') {
                       text += "<p>"+ value['NewValue'] + "</p>"
                       html += '<div class=" ui horizontal divider header"></div><div class="row"></div><a class="ui  label">' + name + '</a> <div class="ui raised segment"> <a class="ui blue ribbon label">Added data</a> <span>' + text + '</span></div>';
                      }
                        else if (value['Type']=='removed') {
                       text += "<p>"+ value['OldValue'] + "</p>"
                       html += '<div class=" ui horizontal divider header"></div><div class="row"></div><a class="ui  label">' + name + '</a> <div class="ui raised segment"> <a class="ui red ribbon label">Removed data</a> <span>' + text + '</span></div>';
                      }

                      
                   
                    else if(typeof value === 'object'){
                      text=""
                      $.each(value, function(entrie, value) {
                      entrie = parseInt(entrie) + 1;
                      $.each(value, function(index, value) {
                         if (value['Type']=='modified') {
                        text += '<div class="ui raised segment"> <a class="ui red ribbon label">Old data : '+index.toUpperCase()+' '+entrie+'</a> <span>' + value['OldValue'] + '</span> <p></p> <a class="ui blue ribbon label">New data : '+index.toUpperCase()+' '+entrie+'</a> '+ value['NewValue'] + ' <p></p> </div>';
                      }
                      else if (value['Type']=='added') {
                        text += '<a class="ui green ribbon label">Added data : '+index.toUpperCase()+' '+entrie+'</a> <span> <p>'+ value['NewValue'] + '</p> </span>'
                      }
                        else if (value['Type']=='removed') {
                       text += '<a class="ui red ribbon label">Removed data : '+index.toUpperCase()+' '+entrie+'</a> <span> <p>'+ value['OldValue'] + '</p> </span>'
                      }
                   });
                    });
                       html += '<div class=" ui horizontal divider header"></div><div class="row"></div><a class="ui  label">' + name+'</a> <div class="ui raised segment">'+text+'</div>';
                    }
                         
                      })
                      append+= version+html+"</div>";

                  })
                  append='<div class="ui styled accordion changelog" >'+append+'</div>';
                  $("#changelog .grid .column ").append(append);
                  $('.ui.accordion.changelog')
                            .accordion()
                          ;



              });




              $('.ui.modal.changelog').modal('show');
          }
      }
  })()
  // Module send_email , Affichage d'un modal permettant d'envoyer un message a un auteur ou au administrateur
  APP.modules.send_email = (function() {
      return {
          send_email: function(doi, name, firstname) {
              $(".ui.modal .header").empty();
              $(".ui.modal .header").append('Contact ' + name + " " + firstname);
              $(".form").append("<input type='hidden' name='doi' value='" + doi + "'><input type='hidden' name='author_name' value='" + name + "'><input type='hidden' name='author_first_name' value='" + firstname + "'>");
              $('.ui.modal.contactauthor').modal('show');
          },
          contact: function() {
              $(".ui.modal .header").empty();
              $(".ui.modal .header").append('Contact us');
              $('.ui.modal.contactus').modal('show');
          }
      }
  })()
  $(document).ready(function() {
      //Allow dropdown
      $('.ui .dropdown.license').dropdown();
      $('.ui .dropdown.publisher').dropdown({
          allowAdditions: true //allo dropdown with other input
      });
      $('.ui .dropdown.scientific_field').dropdown({
          allowAdditions: true
      });
      $('.ui .dropdown.institution').dropdown({
          allowAdditions: true
      });
      $('.ui .dropdown.coordinate').dropdown({
          allowAdditions: true
      });
      //Check in contact form
      $('.contact').form({
          fields: {
              usermail: {
                  identifier: 'User-email',
                  rules: [{
                      type: 'email',
                      prompt: 'Please enter a valid email'
                  }]
              },
              userobject: {
                  identifier: 'User-object',
                  rules: [{
                      type: 'empty',
                      prompt: 'Please enter object'
                  }]
              },
              usermessage: {
                  identifier: 'User-message',
                  rules: [{
                      type: 'empty',
                      prompt: 'Please enter message'
                  }]
              }
          }
      });
      //Datepicker on class date
      $(".date").datepicker({
          dateFormat: "yy-mm-dd",
      });


      APP.modules.search.init();
  });