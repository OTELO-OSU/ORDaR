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
							authorsname.push(file_creator[k]["NAME"]);
							
							}
						}
						else{
							authorsname.push(file_creator["NAME"]);
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
				$("#samplekind").append('<div class="header" >Sample kind</div>');
				for (var k in sample_kind){
					count=sample_kind[k]['doc_count']
					type=sample_kind[k]['key']
					if (type=="" || type==" ") {

					}else{
					$('#samplekind').append('<label  class="item" for="'+type+'"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'" name="sample_kind" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				authors=data['aggregations']['authors']['buckets'];
				$("#authors").append('<div class="header" >Authors</div>');
				for (var k in authors){
					count=authors[k]['doc_count']
					type=authors[k]['key'];
					if (type=="") {

					}else{
					$('#authors').append('<label  class="item" for="'+type+'authors"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'authors" name="authors" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				keywords=data['aggregations']['keywords']['buckets'];
				$("#keywords").append('<div class="header" >Keywords</div>');
				for (var k in keywords){
					count=keywords[k]['doc_count'];
					type=keywords[k]['key'];
					if (type=="") {

					}else{

					$('#keywords').append('<label  class="item" for="'+type+'keywords"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'keywords" name="keywords" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				scientific_field=data['aggregations']['scientific_field']['buckets'];
				$("#scientificfield").append('<div class="header" >Scientific fields</div>');
				for (var k in scientific_field){
					count=scientific_field[k]['doc_count']
					type=scientific_field[k]['key']
					if (type=="") {

					}else{
					$('#scientificfield').append('<label  class="item" for="'+type+'scientificfield"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'scientificfield" name="scientific_field" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				language=data['aggregations']['language']['buckets'];
				$("#language").append('<div class="header" >Languages</div>');
				for (var k in language){
					count=language[k]['doc_count']
					type=language[k]['key'];
					if (type=="") {

					}else{
					$('#language').append('<label  class="item" for="'+type+'language"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'language" name="language" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				filetype=data['aggregations']['filetype']['buckets'];
				$("#filetype").append('<div class="header" >Filetypes</div>');
				for (var k in filetype){
					count=filetype[k]['doc_count']
					type=filetype[k]['key'];
					if (type=="") {

					}else{
					$('#filetype').append('<label  class="item" for="'+type+'filetype"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'filetype" name="filetype" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				accessright=data['aggregations']['access_right']['buckets'];
				$("#accesright").append('<div class="header" >Access right</div>');
				for (var k in accessright){
					count=accessright[k]['doc_count']
					type=accessright[k]['key'];
					if (type=="") {

					}else{
					$('#accesright').append('<label  class="item" for="'+type+'accessright"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'accessright" name="accessright" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
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
				 	facets=undefined;
				 $.each(checked,function(index,value){

				 	if (value.name=="sample_kind") {

				 	var value = value.value;
                    if (samplekind===undefined) {
                      samplekind="INTRO.SAMPLE_KIND.NAME:"+value;

                    }
                    else{
                      samplekind="OR INTRO.SAMPLE_KIND.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets+=" "+samplekind;
                    }
                    else{
                    	facets=samplekind;
                    	
                    }
				 	}
                    if (value.name=="authors") {

				 	var value = value.value;
                    if (authors===undefined) {
                      authors="INTRO.FILE_CREATOR.NAME:"+value;

                    }
                     else{
                      authors=" OR _INTRO.FILE_CREATOR.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets+=" "+authors;
                    }
                    else{
                    	facets=authors;
                    	
                    }
                  }

				if (value.name=="keywords") {

				 	var value = value.value;
                    if (keywords===undefined) {
                      keywords="INTRO.KEYWORDS.NAME:"+value;

                    }
                    else{
                      keywords=keywords+" OR INTRO.KEYWORDS.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+keywords;
                    }
                    else{
                    	facets=keywords;
                    	
                    }
				 	}
                if (value.name=="scientific_field") {

				 	var value = value.value;
                    if (scientific_field===undefined) {
                      scientific_field="INTRO.SCIENTIFIC_FIELD.NAME:"+value;

                    }
                    else{
                      scientific_field=scientific_field+" OR INTRO.SCIENTIFIC_FIELD.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+scientific_field;
                    }
                    else{
                    	facets=scientific_field;
                    	
                    }
				 	}
				if (value.name=="language") {

				 	var value = value.value;
                    if (language===undefined) {
                      language="INTRO.LANGUAGE:"+value;

                    }
                    else{
                      language=language+" OR INTRO.LANGUAGE:"+value;
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+language;
                    }
                    else{
                    	facets=language;
                    	
                    }
				 	}

				 	if (value.name=="filetype") {

				 	var value = value.value;
                    if (filetype===undefined) {
                      filetype='DATA.FILES.FILETYPE:"'+value+'"';

                    }
                    else{
                      filetype=filetype+' OR DATA.FILES.FILETYPE:"'+value+'"';
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
                      access_right="INTRO.ACCESS_RIGHT:"+value;

                    }
                    else{
                      access_right=access_right+" OR INTRO.ACCESS_RIGHT:"+value;
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
				$("#samplekind").append('<div class="header" >Sample kind</div>');
				for (var k in sample_kind){
					count=sample_kind[k]['doc_count']
					type=sample_kind[k]['key']
					if (type=="" || type==" ") {

					}else{
					$('#samplekind').append('<label  class="item" for="'+type+'"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'" name="sample_kind" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				authors=data['aggregations']['authors']['buckets'];
				$("#authors").append('<div class="header" >Authors</div>');
				for (var k in authors){
					count=authors[k]['doc_count']
					type=authors[k]['key'];
					if (type=="") {

					}else{
					$('#authors').append('<label  class="item" for="'+type+'authors"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'authors" name="authors" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				keywords=data['aggregations']['keywords']['buckets'];
				$("#keywords").append('<div class="header" >Keywords</div>');
				for (var k in keywords){
					count=keywords[k]['doc_count']
					type=keywords[k]['key']
				console.log(type);
					if (type=="") {

					}else{

					$('#keywords').append('<label  class="item" for="'+type+'keywords"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'keywords" name="keywords" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				scientific_field=data['aggregations']['scientific_field']['buckets'];
				$("#scientificfield").append('<div class="header" >Scientific fields</div>');
				for (var k in scientific_field){
					count=scientific_field[k]['doc_count']
					type=scientific_field[k]['key']
					if (type=="") {

					}else{
					$('#scientificfield').append('<label  class="item" for="'+type+'scientificfield"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'scientificfield" name="scientific_field" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				language=data['aggregations']['language']['buckets'];
				$("#language").append('<div class="header" >Languages</div>');
				for (var k in language){
					count=language[k]['doc_count']
					type=language[k]['key'];
					if (type=="") {

					}else{
					$('#language').append('<label  class="item" for="'+type+'language"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'language" name="language" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				filetype=data['aggregations']['filetype']['buckets'];
				$("#filetype").append('<div class="header" >Filetypes</div>');
				for (var k in filetype){
					count=filetype[k]['doc_count']
					type=filetype[k]['key'];
					if (type=="") {

					}else{
					$('#filetype').append('<label  class="item" for="'+type+'filetype"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'filetype" name="filetype" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
					}
				}
				accessright=data['aggregations']['access_right']['buckets'];
				$("#accesright").append('<div class="header" >Access right</div>');
				for (var k in accessright){
					count=accessright[k]['doc_count']
					type=accessright[k]['key'];
					if (type=="") {

					}else{
					$('#accesright').append('<label  class="item" for="'+type+'accessright"> <input onclick="APP.modules.mypublications.checkCheckbox()" id="'+type+'accessright" name="accessright" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div>'+count+'</label>')
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
				 	var facets;
				 	var project_name;
				 	var scientific_field;
				 	var access_right;
				 	var filetype;
				 	var keywords;
				 	facets=undefined;
				 $.each(checked,function(index,value){

				 	if (value.name=="sample_kind") {

				 	var value = value.value;
                    if (samplekind===undefined) {
                      samplekind="INTRO.SAMPLE_KIND.NAME:"+value;

                    }
                    else{
                      samplekind="OR INTRO.SAMPLE_KIND.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets+=" "+samplekind;
                    }
                    else{
                    	facets=samplekind;
                    	
                    }
				 	}
                    if (value.name=="authors") {

				 	var value = value.value;
                    if (authors===undefined) {
                      authors="INTRO.FILE_CREATOR.NAME:"+value;

                    }
                     else{
                      authors=" OR _INTRO.FILE_CREATOR.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets+=" "+authors;
                    }
                    else{
                    	facets=authors;
                    	
                    }
                  }

				if (value.name=="keywords") {

				 	var value = value.value;
                    if (keywords===undefined) {
                      keywords="INTRO.KEYWORDS.NAME:"+value;

                    }
                    else{
                      keywords=keywords+" OR INTRO.KEYWORDS.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+keywords;
                    }
                    else{
                    	facets=keywords;
                    	
                    }
				 	}
                if (value.name=="scientific_field") {

				 	var value = value.value;
                    if (scientific_field===undefined) {
                      scientific_field="INTRO.SCIENTIFIC_FIELD.NAME:"+value;

                    }
                    else{
                      scientific_field=scientific_field+" OR INTRO.SCIENTIFIC_FIELD.NAME:"+value;
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+scientific_field;
                    }
                    else{
                    	facets=scientific_field;
                    	
                    }
				 	}
				if (value.name=="language") {

				 	var value = value.value;
                    if (language===undefined) {
                      language="INTRO.LANGUAGE:"+value;

                    }
                    else{
                      language=language+" OR INTRO.LANGUAGE:"+value;
                    }
                    if (facets!==undefined) {
                    	facets=facets+" "+language;
                    }
                    else{
                    	facets=language;
                    	
                    }
				 	}

				 	if (value.name=="filetype") {

				 	var value = value.value;
                    if (filetype===undefined) {
                      filetype='DATA.FILES.FILETYPE:"'+value+'"';

                    }
                    else{
                      filetype=filetype+' OR DATA.FILES.FILETYPE:"'+value+'"';
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
                      access_right="INTRO.ACCESS_RIGHT:"+value;

                    }
                    else{
                      access_right=access_right+" OR INTRO.ACCESS_RIGHT:"+value;
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
      
    }
  })
;
		$("#addauthors").click(function (e) {
 		$("#items").append('<div class="required field" id="items"> <div <div class="three fields"> <div class="field"><label>Author firstname</label><input type="text" name="authors_firstname[]" data-validate="authors_firstname" placeholder="First Name" ></div> <div class="field"><label>Author name</label><input type="text" name="authors_name[]" data-validate="authors_name" placeholder="Family Name, Given names" ></div> <div class="field"><label>Author mail</label><input type="email" name="authors_email[]" data-validate="authors_email" placeholder="Email" required ></div> <button class="ui icon button delete"><i class="remove icon"></i></button> </div> </div>'); });
		$("body").on("click", ".delete", function (e) {
		$(this).parent("div").remove();

});
		$("#addfile").click(function (e) {
 		$("#files").append('<div><input type="file" name="file[]"><button class="ui icon button delete"><i class="remove icon"></i></button></div>'); });
		$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
	var i=0;
	$("#addkeywords").click(function (e) {
	i = $('#keywords input').length;
	if (i <= 2) {
 	$("#keywords").append('<div><input type="text" name="keywords[]"" placeholder="Keyword"><button class="ui icon button delete"><i class="remove icon"></i></button></div>');
	}
 	 });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
	$("#addfundings").click(function (e) {
	i = $('#fundings input').length;
	if (i <= 2) {
 	$("#fundings").append('<div><input type="text" name="fundings[]"" placeholder="Funding"><button class="ui icon button delete"><i class="remove icon"></i></button></div>');
	}
 	 });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});


$("#addinstitution").click(function (e) {
 	$("#institution").append('<div><div class="ui dropdown fluid search selection optgroup institution" "> <input type="hidden" name="institution[]"> <div class="text">Select</div> <i class="dropdown icon"></i> <div class="menu"> <div class="ui horizontal divider">Etablissements publics à caractère scientifique et technologique (EPST)</div> <div class="item">CEMAGREF Centre national du machinisme agricole, du génie rural, des eaux et des forêts</div> <div class="item">CNRS Centre national de la recherche scientifique</div> <div class="item">INED Institut national d\'études démographiques</div> <div class="item">INRA Institut national de la recherche agronomique</div> <div class="item">INRETS Institut national de recherche sur les transports et leur sécurité</div> <div class="item">INRIA Institut national de recherche en informatique et en automatique</div> <div class="item">INSERM Institut national de la santé et de la recherche médicale</div> <div class="item">IRD Institut de recherche pour le développement</div> <div class="item">LCPC Laboratoire central des ponts et chaussées</div> <div class="ui horizontal divider">Etablissements publics à caractère industriel et commercial (EPIC)</div> <div class="item">ADEME Agence de l\'environnement et de la maîtrise de l\'énergie</div> <div class="item">ADIT Agence pour la diffusion de l\'information technologique</div> <div class="item">ANDRA Agence nationale de gestion des déchets radioactifs</div> <div class="item">ANVAR Agence nationale de valorisation de la recherche</div> <div class="item">BRGM Bureau de recherches géologiques et minières</div> <div class="item">CEA Commissariat à l\'énergie atomique</div> <div class="item">CIRAD Centre de coopération international en recherche agronomique</div> <div class="item">CNDP Centre national de documentation pédagogique</div> <div class="item">CNED Centre national d\'enseignement à distance</div> <div class="item">CNES Centre national d\'études spatiales</div> <div class="item">CSI Cité des sciences et de l\'industrie</div> <div class="item">CSTB Centre scientifique et technique du bâtiment</div> <div class="item">CIFREMER Institut français de recherche pour l\'exploitation de la mer</div> <div class="item">CNERIS Institut national de l\'environnement industriel et des risques</div> <div class="item">ONERA Office national d\'études et de recherches aérospatiales</div> <div class="ui horizontal divider">Etablissements publics à caractère administratif (EPA)</div> <div class="item">CEE Centre d\'études de l\'emploi</div> <div class="item">CINES Centre informatique national de l\'enseignement supérieur</div> <div class="item">INRP Institut national de recherche pédagogique</div> <div class="item">EPA Jussieu : désamiantage, mise en sécurité et rénovation du site</div> <div class="ui horizontal divider">Fondations</div> <div class="item">CEPH Centre d\'étude du polymorphisme humain</div> <div class="item">Institut Curie</div> <div class="item">Institut Pasteur</div> <div class="ui horizontal divider">Groupements d\'intérêt public (GIP)</div> <div class="item">ANRS Agence nationale de la recherche contre le SIDA</div> <div class="item">CNG Centre national de génotypage</div> <div class="item">CNS Génoscope - Centre national de séquençage</div> <div class="item">CHMR Fonds de recherche Hoechst Marion Roussel</div> <div class="item">IFRTP Institut pour la recherche et la technologie polaires</div> <div class="item">OST Observatoire des sciences et techniques</div> <div class="item">RENATER Réseau national pour la technologie, l\'enseignement et la recherche</div> </div> </div><button class="ui icon button delete"><i class="remove icon"></i></button></div>'); 
 	$('.ui .dropdown.institution')
  .dropdown({allowAdditions: true})
;
});
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});

$("#addstation").click(function (e) {
 	$("#stations").append('<div> <div> <input type="text" name="station_name[]" placeholder="Name" ></div>  <div><input type="text" name="station_coordonate_system[]"  placeholder="coordonate sytem"></div><div><input type="text" name="station_abbreviation[]"  placeholder="abbreviation"></div><div><input type="text" name="station_longitude[]" placeholder="longitude" ></div><div><input type="text" name="station_latitude[]"  placeholder="latitude"></div><div><input type="text" name="station_elevation[]"  placeholder="elevation"></div><div><textarea name="station_description[]"  placeholder="description"></textarea></div><button class="ui icon button delete"><i class="remove icon"></i></button></div>'); });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});

$("#addmeasurement").click(function (e) {
 	$("#measurements").append('<div class="three fields"> <div class="field"><label>Measurement nature</label><input type="text"  name="measurement_nature[]"  placeholder="Nature" ></div> <div class="field"><label>Measurement abbreviation</label><input type="text"  name="measurement_abbreviation[]" data-validate="measurement_abbreviation" placeholder="Abbreviation" ></div> <div class="field"><label>Measurement unit</label><input type="text"  name="measurement_unit[]" data-validate="measurement_unit" placeholder="Unit" ></div> <button class="ui icon button delete"><i class="remove icon"></i></button> </div>'); });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
$("#addscientificfields").click(function (e) {
	i = $('#scientificfields select').length;
	if (i <= 2) {
 	$("#scientificfields").append('<div><select class="ui fluid search dropdown scientific_field" name="scientific_field[]" > <option value="">Select a field</option> <option value="Addresses">Addresses</option> <option value="Hydrography"> Hydrography</option> <option value="Administrative units"> Administrative units</option> <option value="Land cover"> Land cover</option> <option value="Agricultural and aquaculture facilities"> Agricultural and aquaculture facilities</option> <option value="Land use"> Land use</option> <option value="Area management/restriction/regulation zones and reporting units"> Area management/restriction/regulation zones and reporting units</option> <option value="Meteorological geographical features"> Meteorological geographical features</option> <option value="Atmospheric conditions"> Atmospheric conditions</option> <option value="Mineral resources"> Mineral resources</option> <option value="Bio-geographical regions"> Bio-geographical regions</option> <option value="Natural risk zones"> Natural risk zones</option> <option value="Buildings"> Buildings</option> <option value="Oceanographic geographical features"> Oceanographic geographical features</option> <option value="Cadastral parcels"> Cadastral parcels</option> <option value="Orthoimagery"> Orthoimagery</option> <option value="Coordinate reference systems">Coordinate reference systems</option> <option value="Population distribution — demography">Population distribution — demography</option> <option value="Elevation">Elevation</option> <option value="Production and industrial facilities">Production and industrial facilities</option> <option value="Energy resources">Energy resources</option> <option value="Protected sites">Protected sites</option> <option value="Environmental monitoring facilities">Environmental monitoring facilities</option> <option value="Sea regions">Sea regions</option> <option value="Geographical grid systems">Geographical grid systems</option> <option value="Soil">Soil</option> <option value="Geographical names">Geographical names</option> <option value="Species distribution">Species distribution</option> <option value="Geology">Geology</option> <option value="Statistical units">Statistical units</option> <option value="Habitats and biotopes">Habitats and biotopes</option> <option value="Transport networks">Transport networks</option> <option value="Human health and safety">Human health and safety</option> <option value="Utility and governmental services">Utility and governmental services</option> </select><button class="ui icon button delete"><i class="remove icon"></i></button></div>'); 
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
		console.log(doi);
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



