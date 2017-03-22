var APP = (function(){
	return {
		modules:{},
		init: function(){
			console.log("test")
		}
	}

})();

APP.modules.datatable = (function(){
	return{
		AppendTable : function(data){
			if(data['hits']['total']==0){
				$('#info-noresult').append('No result found!');
				$('#info-noresult').addClass('red');
				$('#info-noresult').show();
				$('#info').remove();
				$('#gridlogo').show();
				$('.easyPaginateNav').remove();
				$('#facets').hide();

			}
			else{
				$('.easyPaginateNav').remove();
				$('#info-noresult').empty();
				$('#info').empty();
				total= data['hits']['total'];
				
				data= data['hits']['hits'];
				for(var k in data){
					var authorsname;
					authorsname=undefined;
					title=data[k]['TITLE']
					data_description=data[k]['DATA_DESCRIPTION']
					filetype=data[k]['FILETYPE']
					accessright=data[k]['ACCESS_RIGHT']
					accessrightdate=data[k]['PUBLICATION_DATE']
					creationdate=data[k]['CREATION_DATE']
					if (accessright=="Open") {
						access='<div class="ui green label">'+accessright+'</div>';
					}
					else if(accessright=="Embargoed"){
						access='<div class="ui orange label" data-tooltip="Available as Open Access after '+accessrightdate+'">'+accessright+'</div>';
					}
					else if (accessright=="Closed"){
						access='<div class="ui red label">'+accessright+'</div>';
					}
					else if (accessright=="Unpublished"){
						access='<div class="ui grey label">'+accessright+'</div>';
					}
					//datedepot='<div class="ui label">'+datedepot+'</div>';
					//filetype='<div class="ui label">'+filetype+'</div>';
					
					file_creator=data[k]['FILE_CREATOR']
					id=data[k]['_id']
					type=data[k]['_type']
					uploaddate=data[k]['UPLOAD_DATE']

					id=id;
					//console.log(file_creator)
					for(var k in file_creator){
						authorsname=[];

						if(file_creator.length>=1){
							for(var k in file_creator){
							author=file_creator[k]["NAME"]+" "+file_creator[k]["FIRST_NAME"];
							authorsname.push(author);

							
							}
						}
						else{
							author=file_creator[k]["NAME"]+" "+file_creator[k]["FIRST_sNAME"];
							authorsname.push(author);
						}
					}
					var authorsnames = document.createElement("ul");

					 for (k in authorsname) {
		                var li = document.createElement("li");
		                li.append(authorsname[k]);
		                authorsnames.append(li);
	            	}
					$('#results').append('<div class="item"> <div class="content">'+access+'<div class="ui blue label">'+creationdate+'</div><div class="row"></div><a href="record?id='+id+'" class="header">'+title+'</a><div class="meta"><h4>Description:</h4><span class="data">'+data_description+'</span></div><div class="row"></div><h4><i class="user icon"></i>Authors:</h4>'+authorsnames.innerHTML+'<div class="extra"> <a href="record?id='+id+'"><div class="ui right floated primary button" >View<i class="right chevron icon"></i></div></a></div><p>Uploaded on '+uploaddate+'</p></div>');
					
				}
				

	     		$('#info').append(total+' result(s) found!');
				$('#info').addClass('green');
				$('#info').show();
				$('#logosearch').hide();
				$('#gridlogo .row').remove();
	     		$('#results').easyPaginate({
				   paginateElement: '.item',
				   elementsPerPage: 10,
				   prevButton:false,
				   nextButton:false,
				  
				});

	     	}
		}
	}
})();

APP.modules.search = (function(){
	return{
		 $_GET: function(param) {
				var vars = {};
				window.location.href.replace( location.hash, '' ).replace( 
					/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
					function( m, key, value ) { // callback
						vars[key] = value !== undefined ? value : '';
					}
				);

				if ( param ) {
					return vars[param] ? vars[param] : null;	
				}
				return vars;
			},
			init: function(){
				$('#info').hide();
				$('#facets').hide();
				$('#info').removeClass('red');
				$('#info').removeClass('green');
				$('#info').empty();
				$('#results').empty();
				$('.easyPaginateNav').remove();
				$('#facets_type').empty();
				var query =APP.modules.search.$_GET('query');

				//var query=$('#query').val()// recuperation de la valeur de l'input
				if (query!=null) {
					
				APP.modules.search.search(query);
				}
			},
			AppendFacets:function(data){
				sample_kind=data['aggregations']['sample_kind']['buckets'];
				if (sample_kind.length==0) {
					$(".ui.card #samplekind").parent().hide();
				}
				$("#samplekind").append('<div class="header" >Sample kind</div>');
				for (var k in sample_kind){
					count=sample_kind[k]['doc_count']
					type=sample_kind[k]['key']
					longtype=type
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="" || type==" ") {

					}else{
					$('#samplekind').append('<label title="'+longtype+'"  class="item" for="'+type+'"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'" name="sample_kind" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				authors=data['aggregations']['authors']['buckets'];
				if (authors.length==0) {
					$(".ui.card #authors").parent().hide();
				}
				$("#authors").append('<div class="header" >Authors</div>');
				for (var k in authors){
					type=authors[k]['key']
					count=authors[k]['doc_count']
					
						longtype=type
						if (type.length>=25) {
							type=type.substring(0,25)+"...";
						}
						if (type=="" || type==" ") {

						}else{
						$('#authors').append('<label title="'+longtype+'"  class="item" for="'+type+'authors"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'authors" name="authors" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
						}

				}
				keywords=data['aggregations']['keywords']['buckets'];
				if (keywords.length==0) {
					$(".ui.card #keywords").parent().hide();
				}
				$("#keywords").append('<div class="header" >Keywords</div>');
				for (var k in keywords){
					count=keywords[k]['doc_count']
					type=keywords[k]['key']
					longtype=type
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="" || type==" ") {

					}else{

					$('#keywords').append('<label title="'+longtype+'"  class="item" for="'+type+'keywords"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'keywords" name="keywords" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				scientific_field=data['aggregations']['scientific_field']['buckets'];
				if (scientific_field.length==0) {
					$(".ui.card #scientificfield").parent().hide();
				}
				$("#scientificfield").append('<div class="header" >Scientific fields</div>');
				for (var k in scientific_field){
					count=scientific_field[k]['doc_count']
					type=scientific_field[k]['key']
					longtype=type;
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="" || type==" ") {

					}else{
					$('#scientificfield').append('<label title="'+longtype+'"  class="item" for="'+type+'scientificfield"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'scientificfield" name="scientific_field" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				language=data['aggregations']['language']['buckets'];
				if (language.length==0) {
					$(".ui.card #language").parent().hide();
				}
				$("#language").append('<div class="header" >Languages</div>');
				for (var k in language){
					count=language[k]['doc_count']
					type=language[k]['key'];
					longtype=type;
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="" || type==" ") {

					}else{
					$('#language').append('<label title="'+longtype+'"  class="item" for="'+type+'language"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'language" name="language" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				filetype=data['aggregations']['filetype']['buckets'];
				if (filetype.length==0) {
					$(".ui.card #filetype").parent().hide();
				}
				$("#filetype").append('<div class="header" >Filetypes</div>');
				for (var k in filetype){
					count=filetype[k]['doc_count']
					type=filetype[k]['key'];
					longtype=type
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="" || type==" ") {

					}else{
					$('#filetype').append('<label title="'+longtype+'"  class="item" for="'+type+'filetype"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'filetype" name="filetype" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				accessright=data['aggregations']['access_right']['buckets'];
				if (accessright.length==0) {
					$(".ui.card #accessright").parent().hide();
				}
				$("#accesright").append('<div class="header" >Access right</div>');
				for (var k in accessright){
					count=accessright[k]['doc_count']
					type=accessright[k]['key'];
					longtype=type
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="Open") {
						color="green";
					}
					if (type=="Closed") {
						color="red";
					}
					if (type=="Embargoed") {
						color="orange";
					}
					if (type=="" || type==" ") {

					}else{
					$('#accesright').append('<label  class="item" for="'+type+'accessright"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'accessright" name="accessright" value="'+type+'" type="checkbox"> <div class="ui '+color+' horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				var creationdatearray=[];
				data= data['hits']['hits'];
				$("#date").append('<div class="header" >Date</div>');
				for(var k in data){
					creationdate=data[k]['CREATION_DATE'].split("-", 2);
					creationdatearray.push(creationdate[0])
				}
				minyear=Math.min.apply(null, creationdatearray);
	        	maxyear=Math.max.apply(null, creationdatearray);
	        	if (minyear==maxyear) {
	        		minyear=maxyear-1;
	        	}
				$('#date').append('<input class="range-slider" value="'+minyear+','+maxyear+'" type="hidden">');

				$('.range-slider').jRange({
				    from:minyear ,
				    to:maxyear,
				    step: 1,
				   	format: '%s',
				    width: 300,
				    showLabels: true,
				    isRange : true,
				     ondragend: function(val){
				     	var query =APP.modules.search.$_GET('query');
				     	range=val.split(",", 2);
						mindate=range[0];
						maxdate=range[1];
						date=" AND INTRO.CREATION_DATE:["+mindate+"-01-01 TO "+maxdate+"-12-31]"
						$('#results').empty();
				     	APP.modules.search.search(query+date,"facets");
				          
				        },
				     onbarclicked: function(val){
						var query =APP.modules.search.$_GET('query');
						range=val.split(",", 2);
						mindate=range[0];
						maxdate=range[1];
						date=" AND INTRO.CREATION_DATE:["+mindate+"-01-01 TO "+maxdate+"-12-31]"
						$('#results').empty();
				     	APP.modules.search.search(query+date,"facets");
				       	}
			});


				$('#facets').show();	
				
		  
	
			},
			checkCheckbox:function(){
				checked=$("#facets input:checked");
				 	var samplekind;
				 	var authors;
				 	var keywords;
				 	var facets;
				 	var project_name;
				 	var scientific_field;
				 	var access_right;
				 	var filetype;
				 	var language;
				 	facets=undefined;
				 $.each(checked,function(index,value){
				 	if (value.name=="sample_kind") {
				 		var value = $(value).parent().attr('title');
					 	 if (samplekind===undefined) {
	                      samplekind='INTRO.SAMPLE_KIND.NAME:"'+value+'"';

	                    }
	                    else{
	                      samplekind='AND INTRO.SAMPLE_KIND.NAME:"'+value+'"';
	                    }
	                    if (facets!==undefined) {
	                    	facets+=" "+samplekind;
	                    }
	                    else{
	                    	facets=samplekind;
	                    	
	                    }
               		 }
				 	
                    if (value.name=="authors") {

				 	var value = $(value).parent().attr('title').split(" ");;
                    if (authors===undefined) {
                      authors='INTRO.FILE_CREATOR.NAME:"'+value[0]+'" AND INTRO.FILE_CREATOR.FIRST_NAME:"'+value[1]+'"';

                    }
                     else{
                      authors=' AND INTRO.FILE_CREATOR.NAME:"'+value[0]+'" AND INTRO.FILE_CREATOR.FIRST_NAME:"'+value[1]+'"';
                    }
                    if (facets!==undefined) {
                    	facets+=" "+authors;
                    }
                    else{
                    	facets=authors;
                    	
                    }
                  }

				if (value.name=="keywords") {

				 	var value = $(value).parent().attr('title');
                    if (keywords===undefined) {
                      keywords='INTRO.KEYWORDS.NAME:"'+value+'"';

                    }
                    else{
                      keywords=keywords+' AND INTRO.KEYWORDS.NAME:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+keywords;
                    }
                    else{
                    	facets=keywords;
                    	
                    }
				 	}
                if (value.name=="scientific_field") {

				 	var value = $(value).parent().attr('title');
                    if (scientific_field===undefined) {
                      scientific_field='INTRO.SCIENTIFIC_FIELD.NAME:"'+value+'"';

                    }
                    else{
                      scientific_field=scientific_field+' AND INTRO.SCIENTIFIC_FIELD.NAME:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+scientific_field;
                    }
                    else{
                    	facets=scientific_field;
                    	
                    }
				 	}
				if (value.name=="language") {

				 	var value = $(value).parent().attr('title');
                    if (language===undefined) {
                      language='INTRO.LANGUAGE:"'+value+'"';

                    }
                    else{
                      language=language+' AND INTRO.LANGUAGE:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+language;
                    }
                    else{
                    	facets=language;
                    	
                    }
				 	}

				 	if (value.name=="filetype") {

				 	var value = $(value).parent().attr('title');
                    if (filetype===undefined) {
                      filetype='DATA.FILES.FILETYPE:"'+value+'"';

                    }
                    else{
                      filetype=filetype+' AND DATA.FILES.FILETYPE:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+filetype;
                    }
                    else{
                    	facets=filetype;
                    	
                    }
				 	}
				 	if (value.name=="accessright") {

				 	var value = value.value;
                    if (access_right===undefined) {
                      access_right='INTRO.ACCESS_RIGHT:"'+value+'"';

                    }
                    else{
                      access_right=access_right+' AND INTRO.ACCESS_RIGHT:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+"  "+access_right;
                    }
                    else{
                    	facets=access_right;
                    	
                    }
				 	}



                    })

					

                    if (facets===undefined) {
                        facets="";
                    }
                    else{

                   facets= " AND "+facets
                    }
                   

                    var query =APP.modules.search.$_GET('query');

                   // var query=$('#query').val()// recuperation de la valeur de l'input
                    $('#results').empty();


                    APP.modules.search.search(query+facets,"facets");
			

			},
			search: function(query,facets){
				$.post("index.php/getinfo",
		        {
		          query: query,
		        }, 
		        function(data){
		        	data=JSON.parse(data)
		        	if (!facets) {

		        	APP.modules.search.AppendFacets(data);
		        	}
		        	APP.modules.datatable.AppendTable(data);
		        })
			},
			element:function(element){
				console.log(element)
			}
			/*get_info_for_dataset:function(id){
				$.post("index.php/getinfodataset",
		        {
		          id: id,
		        }, 
		        function(data){
		        window.location.replace("record/"+id);
		        data=JSON.parse(data)
		        data=data['_source'];
		        $('.viewdataset .header').empty();
		        $('.viewdataset .content').empty();
		        $('.viewdataset .data').empty();
			    $('.viewdataset .header').append(data["INTRO"]["TITLE"]);
				accessright=data['INTRO']['ACCESS_RIGHT']
				creationdate=data['INTRO']['CREATION_DATE']
				uploaddate=data['INTRO']['UPLOAD_DATE']

				file_creator=data['INTRO']['FILE_CREATOR']
				id="'"+type+"/"+id+"'";
				filetype=data['INTRO']['FILETYPE']
				accessrightdate=data['INTRO']['PUBLICATION_DATE']
				creationdate=data['INTRO']['CREATION_DATE']
				filetype='<div class="ui label">'+filetype+'</div>';
				for(var k in file_creator){
					authorsname=[];

					if(file_creator.length>=1){
						for(var k in file_creator){
						authorsname.push(file_creator[k]["NAME"]);
						
						}
					}
					else{
						authorsname.push(file_creator["NAME"][0]);
					}
				}
				var authorsnames = document.createElement("ul");

				 for (k in authorsname) {
	                var li = document.createElement("li");
	                li.append(authorsname[k]);
	                authorsnames.append(li);
            	}
					if (accessright=="Open") {
						access='<div class="ui green label">'+accessright+'</div>';
						files="  <div class='event'><div class='content'><div class='summary'><a>"+data['DATA']['FILE'][0]['DATA_URL']+"</a></div></div></div>";
					}
					else if(accessright=="Embargoed"){
						access='<div class="ui red label" data-tooltip="Available as Open Access after '+accessrightdate+'">'+accessright+'</div>';
						files='<div class="ui red message">Data are not available</div>'

					}
					else{
						access='<div class="ui red label">'+accessright+'</div>';
						files='<div class="ui red message">Data are not available</div>';
					}
			 	$('.viewdataset .content').append(access+'<div class="ui blue label">'+creationdate+'</div>'+filetype+'<p><div class="meta"><h4>Description:</h4><span class="data">'+data_description+'</span></div><div class="row"></div><h4><i class="user icon"></i>Authors:</h4>'+authorsnames.innerHTML+'</div><div class="ui container cards"><div class="ui fluid card"><div class="content files"><div class="header ">Files</div>'+files+'</div></div><div class="ui fluid card"><div class="content"><div class="header">Export</div></div></div><p>Uploaded on '+uploaddate+'</p></div>');
		        })
	
			}*/
		}


})()


APP.modules.mypublications = (function(){
	return{
			init: function(){
				var query=APP.modules.mypublications.$_GET('query');
				APP.modules.mypublications.search(query);
			},
			AppendFacets:function(data){
				$('#info').hide();
				$('#facets').hide();
				$('#info').removeClass('red');
				$('#info').removeClass('green');
				$('#info').empty();
				$('#results').empty();
				$('.easyPaginateNav').remove();
				$('#facets_type').empty();
				sample_kind=data['aggregations']['sample_kind']['buckets'];
				if (sample_kind.length==0) {
					$(".ui.card #samplekind").parent().hide();
				}
				$("#samplekind").append('<div class="header" >Sample kind</div>');
				for (var k in sample_kind){
					count=sample_kind[k]['doc_count']
					type=sample_kind[k]['key']
					longtype=type
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="" || type==" ") {

					}else{
					$('#samplekind').append('<label title="'+longtype+'"  class="item" for="'+type+'"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'" name="sample_kind" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				authors=data['aggregations']['authors']['buckets'];
				if (authors.length==0) {
					$(".ui.card #authors").parent().hide();
				}
				$("#authors").append('<div class="header" >Authors</div>');
				for (var k in authors){
					type=authors[k]['key']
					count=authors[k]['doc_count']
					
						longtype=type
						if (type.length>=25) {
							type=type.substring(0,25)+"...";
						}
						if (type=="" || type==" ") {

						}else{
					$('#authors').append('<label title="'+longtype+'"  class="item" for="'+type+'authors"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'authors" name="authors" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
					

				}
				keywords=data['aggregations']['keywords']['buckets'];
				if (keywords.length==0) {
					$(".ui.card #keywords").parent().hide();
				}
				$("#keywords").append('<div class="header" >Keywords</div>');
				for (var k in keywords){
					count=keywords[k]['doc_count']
					type=keywords[k]['key']
					longtype=type
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="") {

					}else{

					$('#keywords').append('<label title="'+longtype+'"  class="item" for="'+type+'keywords"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'keywords" name="keywords" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				scientific_field=data['aggregations']['scientific_field']['buckets'];
				if (scientific_field.length==0) {
					$(".ui.card #scientificfields").parent().hide();
				}
				$("#scientificfield").append('<div class="header" >Scientific fields</div>');
				for (var k in scientific_field){
					count=scientific_field[k]['doc_count']
					type=scientific_field[k]['key']
					longtype=type;
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="") {

					}else{
					$('#scientificfield').append('<label title="'+longtype+'"  class="item" for="'+type+'scientificfield"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'scientificfield" name="scientific_field" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				language=data['aggregations']['language']['buckets'];
				if (language.length==0) {
					$(".ui.card #language").parent().hide();
				}
				$("#language").append('<div class="header" >Languages</div>');
				for (var k in language){
					count=language[k]['doc_count']
					type=language[k]['key'];
					longtype=type;
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="") {

					}else{
					$('#language').append('<label title="'+longtype+'"  class="item" for="'+type+'language"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'language" name="language" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				filetype=data['aggregations']['filetype']['buckets'];
				if (filetype.length==0) {
					$(".ui.card #filetype").parent().hide();
				}
				$("#filetype").append('<div class="header" >Filetypes</div>');
				for (var k in filetype){
					count=filetype[k]['doc_count']
					type=filetype[k]['key'];
					longtype=type
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="") {

					}else{
					$('#filetype').append('<label title="'+longtype+'"  class="item" for="'+type+'filetype"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'filetype" name="filetype" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				accessright=data['aggregations']['access_right']['buckets'];
				if (accessright.length==0) {
					$(".ui.card #accessright").parent().hide();
				}
				$("#accesright").append('<div class="header" >Access right</div>');
				for (var k in accessright){
					count=accessright[k]['doc_count']
					type=accessright[k]['key'];
					longtype=type
					if (type=="Open") {
						color="green";
					}
					if (type=="Closed") {
						color="red";
					}
					if (type=="Embargoed") {
						color="orange";
					}
					if (type=="Unpublished") {
						color="grey";
					}
					if (type.length>=25) {
						type=type.substring(0,20)+"...";
					}
					if (type=="") {

					}else{
					$('#accesright').append('<label  class="item" for="'+type+'accessright"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'accessright" name="accessright" value="'+type+'" type="checkbox"> <div class="ui '+color+' horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				var creationdatearray=[];
				data= data['hits']['hits'];
				$("#date").append('<div class="header" >Date</div>');
				for(var k in data){
					creationdate=data[k]['CREATION_DATE'].split("-", 2);
					creationdatearray.push(creationdate[0])
				}
				minyear=Math.min.apply(null, creationdatearray);
	        	maxyear=Math.max.apply(null, creationdatearray);
	        	if (minyear==maxyear) {
	        		minyear=maxyear-1;
	        	}
				$('#date').append('<input class="range-slider" value="'+minyear+','+maxyear+'" type="hidden">');

				$('.range-slider').jRange({
				    from:minyear ,
				    to:maxyear,
				    step: 1,
				   	format: '%s',
				    width: 300,
				    showLabels: true,
				    isRange : true,
				     ondragend: function(val){
				     	var query ="*";
				     	range=val.split(",", 2);
						mindate=range[0];
						maxdate=range[1];
						date=" AND INTRO.CREATION_DATE:["+mindate+"-01-01 TO "+maxdate+"-12-31]"
						$('#results').empty();
				     	APP.modules.mypublications.search(query+date,"facets");
				          
				        },
				     onbarclicked: function(val){
						var query ="*";
						range=val.split(",", 2);
						mindate=range[0];
						maxdate=range[1];
						date=" AND INTRO.CREATION_DATE:["+mindate+"-01-01 TO "+maxdate+"-12-31]"
						$('#results').empty();
				     	APP.modules.mypublications.search(query+date,"facets");
				       	}
			});


				$('#facets').show();	
				
		  
	
			},
			checkCheckbox:function(){
				checked=$("#facets input:checked");
				 	var samplekind;
				 	var authors;
				 	var facets;
				 	var project_name;
				 	var scientific_field;
				 	var access_right;
				 	var filetype;
				 	var keywords;
				 	var language;
				 	facets=undefined;
				 $.each(checked,function(index,value){

				 	if (value.name=="sample_kind") {

				 	var value = $(value).parent().attr('title');
                    if (samplekind===undefined) {
                      samplekind='INTRO.SAMPLE_KIND.NAME:"'+value+'"';

                    }
                    else{
                      samplekind='AND INTRO.SAMPLE_KIND.NAME:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets+=" "+samplekind;
                    }
                    else{
                    	facets=samplekind;
                    	
                    }
				 	}
                   
                    if (value.name=="authors") {


				 	var value = $(value).parent().attr('title').split(" ");;
                    if (authors===undefined) {
                      authors='INTRO.FILE_CREATOR.NAME:"'+value[0]+'" AND INTRO.FILE_CREATOR.FIRST_NAME:"'+value[1]+'"';

                    }
                     else{
                      authors=' AND INTRO.FILE_CREATOR.NAME:"'+value[0]+'" AND INTRO.FILE_CREATOR.FIRST_NAME:"'+value[1]+'"';
                    }
                    if (facets!==undefined) {
                    	facets+=" "+authors;
                    }
                    else{
                    	facets=authors;
                    	
                    }
                  }

				if (value.name=="keywords") {

				 	var value = $(value).parent().attr('title');
                    if (keywords===undefined) {
                      keywords='INTRO.KEYWORDS.NAME:"'+value+'"';

                    }
                    else{
                      keywords=keywords+' AND INTRO.KEYWORDS.NAME:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+keywords;
                    }
                    else{
                    	facets=keywords;
                    	
                    }
				 	}
                if (value.name=="scientific_field") {
                	

				 	var value = $(value).parent().attr('title');
                    if (scientific_field===undefined) {
                      scientific_field='INTRO.SCIENTIFIC_FIELD.NAME:"'+value+'"';

                    }
                    else{
                      scientific_field=scientific_field+' AND INTRO.SCIENTIFIC_FIELD.NAME:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+scientific_field;
                    }
                    else{
                    	facets=scientific_field;
                    	
                    }
				 	}
				if (value.name=="language") {

				 	var value = $(value).parent().attr('title');
                    if (language===undefined) {
                      language='INTRO.LANGUAGE:"'+value+'"';

                    }
                    else{
                      language=language+' AND INTRO.LANGUAGE:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+language;
                    }
                    else{
                    	facets=language;
                    	
                    }
				 	}

				 	if (value.name=="filetype") {

				 	var value = $(value).parent().attr('title');
                    if (filetype===undefined) {
                      filetype='DATA.FILES.FILETYPE:"'+value+'"';

                    }
                    else{
                      filetype=filetype+' AND DATA.FILES.FILETYPE:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+filetype;
                    }
                    else{
                    	facets=filetype;
                    	
                    }
				 	}
				 	if (value.name=="accessright") {

				 	var value = value.value;
                    if (access_right===undefined) {
                      access_right='INTRO.ACCESS_RIGHT:"'+value+'"';

                    }
                    else{
                      access_right=access_right+' AND INTRO.ACCESS_RIGHT:"'+value+'"';
                    }
                    if (facets!==undefined) {
                    	facets=facets+"  "+access_right;
                    }
                    else{
                    	facets=access_right;
                    	
                    }
				 	}


                    })

                    if (facets===undefined) {
                        facets="";
                    }
                    else{

                   facets= " AND ("+facets+")"
                    }
                   
                    $('#results').empty();

                    APP.modules.mypublications.search("*"+facets,'facets');


			},
			 $_GET: function(param) {
				var vars = {};
				window.location.href.replace( location.hash, '' ).replace( 
					/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
					function( m, key, value ) { // callback
						vars[key] = value !== undefined ? value : '';
					}
				);

				if ( param ) {
					return vars[param] ? vars[param] : null;	
				}
				return vars;
			},
			search: function(query,facets){
				//var query=APP.modules.mypublications.$_GET('query');
				$.post("index.php/getmypublications",
		        {
		        	query:query
		        }, 
		        function(data){
		        	data=JSON.parse(data);
		        	if (!facets) {

		        	APP.modules.mypublications.AppendFacets(data);
		        	}
		        	APP.modules.datatable.AppendTable(data);
		        })
			}
		}


})()


APP.modules.upload=(function(){
return{
	init:function(){
	$('.ui.accordion').accordion();
		$('.ui .form')
  .form({
    fields: {
      title: {
        identifier: 'title',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter title'
          }
        ]
      },
      creation_date: {
        identifier: 'creation_date',
        rules: [
          {
            type   : 'regExp[^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])*$]',
            prompt : 'Please enter creation date'
          }
        ]
      },
       authors_firstname: {
        identifier: 'authors_firstname',
        rules: [
          {
            type   : 'regExp[^[a-zA-Z][a-zA-Z -.,]*$]',
            prompt : 'Please enter a valid firstname'
          
          }
        ]
      },
       authors_name: {
        identifier: 'authors_name',
        rules: [
          {
            type   : 'regExp[^[a-zA-Z][a-zA-Z -.,]*$]',
            prompt : 'Please enter a valid name'
          }
        ]
      },
       authors_email: {
        identifier: 'authors_email',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter authors email'
          }
        ]
      },
       description: {
        identifier: 'description',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter description'
          }
        ]
      },
      scientific_field: {
        identifier: 'scientific_field',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter a scientific field'
          }
        ]
      },
       measurement_nature: {
        identifier: 'measurement_nature',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter measurement nature'
          }
        ]
      },
       measurement_abbreviation: {
        identifier: 'measurement_abbreviation',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter measurement abbreviation'
          }
        ]
      },
       measurement_unit: {
        identifier: 'measurement_unit',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter measurement unit'
          }
        ]
      },
      license: {
        identifier: 'license',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter license'
          }
        ]
      },
       selectaccessright: {
        identifier: 'access_right',
        rules: [
          {
            type   : 'checked',
            prompt : 'Please select accessright'
          }
        ]
      },
      institutions: {
        identifier: 'institution',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter institution'
          }
        ]
      },
      publisher: {
        identifier: 'publisher',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter a publisher'
          }
        ]
      },
      
    }
  })
;
		$("#addauthors").click(function (e) {
 		$("#authors").append('<div class="required field" > <div class="three fields"> <div class="field"><label>Author firstname</label><input type="text" name="authors_firstname[]" data-validate="authors_firstname" placeholder="First Name" ></div> <div class="field"><label>Author name</label><input type="text" name="authors_name[]" data-validate="authors_name" placeholder="Family Name, Given names" ></div> <div class="field"><label>Author mail</label><input type="email" name="authors_email[]" data-validate="authors_email" placeholder="Email" required ></div> <div class="ui icon delete center"><i class="remove icon"></i></div> </div> </div>'); });
		$("body").on("click", ".delete", function (e) {
		$(this).parent("div").remove();

});
		$("#addfile").click(function (e) {
 		$("#files").append('<div class="row"></div><div class="ui input"><input type="file" name="file[]"><div class="ui icon delete"><i class="remove icon"></i></div> </div>'); });
		$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
	var i=0;
	$("#addkeywords").click(function (e) {
	i = $('#keywords input').length;
	if (i <= 2) {
 	$("#keywords").append('<div class="ui input"><input type="text" name="keywords[]"" placeholder="Keyword"><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
	}
 	 });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
	$("#addfundings").click(function (e) {
	i = $('#fundings input').length;
	if (i <= 2) {
 	$("#fundings").append('<div class="ui input"><input type="text" name="fundings[]"" placeholder="Funding"><div class="ui icon delete"><i class="remove icon"></i></div> </div>');
	}
 	 });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});


$("#addinstitution").click(function (e) {
 	$("#institution").append('<div class="ui input"><div class="ui dropdown fluid search selection optgroup institution" "> <input type="hidden" name="institution[]"> <div class="text">Select</div> <i class="dropdown icon"></i> <div class="menu"> <div class="item">ADIT Agency for the dissemination of technological information</div> <div class="item">ANDRA National Agency for Radioactive Waste Management</div> <div class="item">B.R.G.M. THE FRENCH GEOLOGICAL SURVEY</div> <div class="item">C.E.A. The French Alternative Energies and Atomic Energy Commission</div> <div class="item">C.E.E. Center for Employment Studies</div> <div class="item">CEPH Human plymorphism Study Center</div> <div class="item">CIRAD Agricultural Research for development</div> <div class="item">Cité des sciences et Palais de la découverte</div> <div class="item">National Museum of the History of Immigration</div> <div class="item">CNES National Center for Space Studies</div> <div class="item">C.N.R.S. National Center for sciences Research</div> <div class="item">GENOPOLE France’s leading biocluster for biotechnologies and research in genomics and genetics</div> <div class="item">IFE French Institute of Education</div> <div class="item">IFREMER French Research Institute for Exploitation of the Sea</div> <div class="item">IFPEN Research and training player in the ﬁelds of energy, transport and the environment</div> <div class="item">IFSTTAR French Institute of science and technology for transport, development and networks</div> <div class="item">INCA National Institute of cancer</div> <div class="item">INED National Institute for Demographic Studies</div> <div class="item">INERIS National Institute for the Industrial Environment and Risks</div> <div class="item">INRA National Institute of Agronomic Research</div> <div class="item">INRIA National Institute for Research in Computer Science and Automation</div> <div class="item">INSERM National Institute of Health and Medical Research</div> <div class="item">Curie Institute</div> <div class="item">Pasteur Institute</div> <div class="item">IPEV French Polar Institute Paul Emile Victor</div> <div class="item">I.R.D. Research Institute for Development</div> <div class="item">I.R.S.N. Institute for Radiation Protection and Nuclear Safety</div> <div class="item">IRSTEA National Science and Technology Research Institute for Environment and Agriculture</div> <div class="item">M.N.H.N National Museum of Natural History</div> <div class="item">Quai Branly Museum</div> <div class="item">ONERA National Office for Aeronautical Studies and Research</div> <div class="item">OSEO</div> <div class="item">O.S.T. Science and Technology Observatory</div> <div class="item">RENATER National Telecommunication Network for Technology, Education and Research</div></div> </div><div class="ui icon delete"><i class="remove icon"></i></div> </div>'); 
 	$('.ui .dropdown.institution')
  .dropdown({allowAdditions: true})
;
});
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});

$("#addsampling_point").click(function (e) {
 	$("#sampling_points").append('<div> <div class="ui input"> <input type="text" name="sampling_point_name[]" placeholder="Name" ></div>  <div><input type="text" name="sampling_point_coordonate_system[]"  placeholder="coordonate sytem"></div><div><input type="text" name="sampling_point_abbreviation[]"  placeholder="abbreviation"></div><div><input type="text" name="sampling_point_longitude[]" placeholder="longitude" ></div><div><input type="text" name="sampling_point_latitude[]"  placeholder="latitude"></div><div><input type="text" name="sampling_point_elevation[]"  placeholder="elevation"></div><div><textarea name="sampling_point_description[]"  placeholder="description"></textarea></div><div class="ui icon delete"><i class="remove icon"></i></div> </div>'); });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
$("#addsamplingdate").click(function (e) {
	$("#sampling_date").append('        <div class="ui input"><input type="text"  class="date" name="sampling_date[]" placeholder="Sampling date" ><div class="ui icon delete"><i class="remove icon"></i></div> </div>'); });
$("body").on("click", ".delete", function (e) {
$(this).parent("div").remove();
});
$("#addsamplekind").click(function (e) {
	$("#sample_kind").append('<div class="ui input"><input type="text" name="sample_kind[]" placeholder="Sample kind" ><div class="ui icon delete"><i class="remove icon"></i></div> </div>'); });
$("body").on("click", ".delete", function (e) {
$(this).parent("div").remove();
});

$("#addmeasurement").click(function (e) {
 	$("#measurements").append('<div class="three fields"> <div class="field"><label>Measurement nature</label><input type="text"  name="measurement_nature[]"  placeholder="Nature" ></div> <div class="field"><label>Measurement abbreviation</label><input type="text"  name="measurement_abbreviation[]" data-validate="measurement_abbreviation" placeholder="Abbreviation" ></div> <div class="field"><label>Measurement unit</label><input type="text"  name="measurement_unit[]" data-validate="measurement_unit" placeholder="Unit" ></div> <div class="ui icon delete center"><i class="remove icon"></i></div>  </div>'); });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
$("#addscientificfields").click(function (e) {
	i = $('#scientificfields select').length;
	if (i <= 2) {
 	$("#scientificfields").append('<div class="ui input"><select class="ui fluid search dropdown scientific_field" name="scientific_field[]" > <option value="">Select a field</option> <option value="Addresses">Addresses</option> <option value="Hydrography"> Hydrography</option> <option value="Administrative units"> Administrative units</option> <option value="Land cover"> Land cover</option> <option value="Agricultural and aquaculture facilities"> Agricultural and aquaculture facilities</option> <option value="Land use"> Land use</option> <option value="Area management/restriction/regulation zones and reporting units"> Area management/restriction/regulation zones and reporting units</option> <option value="Meteorological geographical features"> Meteorological geographical features</option> <option value="Atmospheric conditions"> Atmospheric conditions</option> <option value="Mineral resources"> Mineral resources</option> <option value="Bio-geographical regions"> Bio-geographical regions</option> <option value="Natural risk zones"> Natural risk zones</option> <option value="Buildings"> Buildings</option> <option value="Oceanographic geographical features"> Oceanographic geographical features</option> <option value="Cadastral parcels"> Cadastral parcels</option> <option value="Orthoimagery"> Orthoimagery</option> <option value="Coordinate reference systems">Coordinate reference systems</option> <option value="Population distribution — demography">Population distribution — demography</option> <option value="Elevation">Elevation</option> <option value="Production and industrial facilities">Production and industrial facilities</option> <option value="Energy resources">Energy resources</option> <option value="Protected sites">Protected sites</option> <option value="Environmental monitoring facilities">Environmental monitoring facilities</option> <option value="Sea regions">Sea regions</option> <option value="Geographical grid systems">Geographical grid systems</option> <option value="Soil">Soil</option> <option value="Geographical names">Geographical names</option> <option value="Species distribution">Species distribution</option> <option value="Geology">Geology</option> <option value="Statistical units">Statistical units</option> <option value="Habitats and biotopes">Habitats and biotopes</option> <option value="Transport networks">Transport networks</option> <option value="Human health and safety">Human health and safety</option> <option value="Utility and governmental services">Utility and governmental services</option> </select><div class="ui icon delete"><i class="remove icon"></i></div> </div>'); 
	$('.ui .dropdown.scientific_field')
  .dropdown({allowAdditions: true})
;
	}
 });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
	},
	checkformbutton:function(){
			    if (document.getElementById('embargoed').checked) {
			        document.getElementById('date_end').style.display = 'block';
			        document.getElementById('date_end').required = true;
			    }
			    else document.getElementById('date_end').style.display = 'none';
	}
}})()


APP.modules.preview=(function(){
return{
	previewdocument:function(link){
		$("#preview").empty();
		$("#preview").append(  '<iframe src="'+link+'" style="width:900px; height:550px;" frameborder="0"></iframe>');
		$('.ui.modal.preview').modal('show');
		}
		
}})()


APP.modules.send_email=(function(){
return{
	send_email:function(doi,name,firstname){
		$(".ui.modal .header").empty();
		$(".ui.modal .header").append('Contact '+name+" "+firstname);
		$(".form").append("<input type='hidden' name='doi' value='"+doi+"'><input type='hidden' name='author_name' value='"+name+"'><input type='hidden' name='author_first_name' value='"+firstname+"'>");
		$('.ui.modal.contactauthor').modal('show');
		},
	contact:function(){
		$(".ui.modal .header").empty();
		$(".ui.modal .header").append('Contact us');
		$('.ui.modal.contactus').modal('show');
	}
		
}})()







$(document).ready(function(){

$('.ui .dropdown.license')
  .dropdown()
;
$('.ui .dropdown.publisher')
  .dropdown({allowAdditions: true})
;
$('.ui .dropdown.scientific_field')
  .dropdown({allowAdditions: true})
;
$('.ui .dropdown.institution')
  .dropdown({allowAdditions: true})
;

$('.contact')
  .form({
    fields: {
      usermail: {
        identifier: 'User-email',
        rules: [
          {
            type   : 'email',
            prompt : 'Please enter a valid email'
          }
        ]
      },
      userobject: {
        identifier: 'User-object',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter object'
          }
        ]
      },
      usermessage: {
        identifier: 'User-message',
        rules: [
          {
            type   : 'empty',
            prompt : 'Please enter message'
          }
        ]
      }
  	}
  });


	APP.init();

	APP.modules.search.init();
  	
  		    $(".date").datepicker({dateFormat: "yy-mm-dd"});

  	


});



