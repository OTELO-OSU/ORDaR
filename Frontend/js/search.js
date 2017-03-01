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
				$('#info').append('No result found!');
				$('#info').addClass('red');
				$('#info').show();
				$('.easyPaginateNav').remove();
				$('#facets').hide();

			}
			else{
				$('.easyPaginateNav').remove();
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
						access='<div class="ui red label" data-tooltip="Available as Open Access after '+accessrightdate+'">'+accessright+'</div>';
					}
					else{
						access='<div class="ui red label">'+accessright+'</div>';
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
					$('#results').append('<div class="item"> <div class="content">'+access+'<div class="ui blue label">'+creationdate+'</div><div class="row"></div><a class="header">'+title+'</a><div class="meta"><h4>Description:</h4><span class="data">'+data_description+'</span></div><div class="row"></div><h4><i class="user icon"></i>Authors:</h4>'+authorsnames.innerHTML+'<div class="extra"><div class="ui label">'+type+'</div> <a href="record?id='+id+'"><div class="ui right floated primary button" >View<i class="right chevron icon"></i></div></a></div><p>Uploaded on '+uploaddate+'</p></div>');
					
				}
				

	     		$('#info').append(total+' result(s) found!');
				$('#info').addClass('green');
				$('#info').show();
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
					if (type=="") {

					}else{
					$('#samplekind').append('<label  class="item" for="'+type+'"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'" name="sample_kind" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div></input>'+count+'</label>')
					}
				}
				authors=data['aggregations']['authors']['buckets'];
				$("#authors").append('<div class="header" >Authors</div>');
				for (var k in authors){
					count=authors[k]['doc_count']
					type=authors[k]['key'];
					$('#authors').append('<label  class="item" for="'+type+'authors"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'authors" name="authors" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div></input>'+count+'</label>')
				}
				keywords=data['aggregations']['keywords']['buckets'];
				$("#keywords").append('<div class="header" >Keywords</div>');
				for (var k in keywords){
					count=keywords[k]['doc_count']
					type=keywords[k]['key']
				console.log(type);
					if (type=="") {

					}else{

					$('#keywords').append('<label  class="item" for="'+type+'keywords"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'keywords" name="keywords" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div></input>'+count+'</label>')
					}
				}
				scientific_field=data['aggregations']['scientific_field']['buckets'];
				$("#scientificfield").append('<div class="header" >Scientific fields</div>');
				for (var k in scientific_field){
					count=scientific_field[k]['doc_count']
					type=scientific_field[k]['key']
					$('#scientificfield').append('<label  class="item" for="'+type+'scientificfield"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'scientificfield" name="scientific_field" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div></input>'+count+'</label>')
				}
				language=data['aggregations']['language']['buckets'];
				$("#language").append('<div class="header" >Languages</div>');
				for (var k in language){
					count=language[k]['doc_count']
					type=language[k]['key'];
					$('#language').append('<label  class="item" for="'+type+'language"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'language" name="language" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div></input>'+count+'</label>')
				}
				filetype=data['aggregations']['filetype']['buckets'];
				$("#filetype").append('<div class="header" >Filetypes</div>');
				for (var k in filetype){
					count=filetype[k]['doc_count']
					type=filetype[k]['key'];
					$('#filetype').append('<label  class="item" for="'+type+'filetype"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'filetype" name="filetype" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div></input>'+count+'</label>')
				}
				accessright=data['aggregations']['access_right']['buckets'];
				$("#accesright").append('<div class="header" >Access right</div>');
				for (var k in accessright){
					count=accessright[k]['doc_count']
					type=accessright[k]['key'];
					$('#accesright').append('<label  class="item" for="'+type+'accessright"> <input onclick="APP.modules.search.checkCheckbox()" id="'+type+'accessright" name="accessright" value="'+type+'" type="checkbox"> <div class="ui blue horizontal label">'+type+'</div></input>'+count+'</label>')
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
				 	facets=undefined;
				 $.each(checked,function(index,value){

				 	if (value.name=="sample_kind") {

				 	var value = value.value;
                    if (samplekind===undefined) {
                      samplekind="INTRO.SAMPLE_KIND.NAME:"+value;

                    }
                    else{
                      samplekind="OR SAMPLE_KIND:"+value;
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
                      keywords="INTRO.KEYWORDS:"+value;

                    }
                    else{
                      keywords=keywords+" OR INTRO.KEYWORDS:"+value;
                    }
                    if (facets!==undefined) {
                    	facets=facets+" AND "+keywords;
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
                    	facets=facets+" AND "+scientific_field;
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
                    	facets=facets+" AND "+language;
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
                    	facets=facets+" AND "+filetype;
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
                    	facets=facets+" AND "+access_right;
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
				
				APP.modules.mypublications.search();
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
			search: function(){
				var query=APP.modules.mypublications.$_GET('query');
				$.post("index.php/getmypublications",
		        {}, 
		        function(data){
		        	data=JSON.parse(data);
		        	APP.modules.datatable.AppendTable(data);
		        })
			}
		}


})()


APP.modules.upload=(function(){
return{
	init:function(){
		$("#addauthors").click(function (e) {
 		$("#items").append('<div>	 <div><input type="text" name="authors-firstname[]" placeholder="First Name" required=""></div><input name="authors-name[]"  placeholder="Family Name, Given names" required="" type="text" /><input type="email" name="authors-email[]" placeholder="Email"  required=""><button class="ui icon button delete"><i class="remove icon"></i></button></div>'); });
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
	i+=1;
	if (i <= 2) {
 	$("#keywords").append('<div><input type="text" name="keywords[]"" placeholder="Keyword"><button class="ui icon button delete"><i class="remove icon"></i></button></div>');
	}
 	 });
	$("body").on("click", ".delete", function (e) {
	$(this).parent("div").remove();
});
	},
	checkformbutton:function(){
			    if (document.getElementById('embargoed').checked) {
			        document.getElementById('date_end').style.display = 'block';
			    }
			    else document.getElementById('date_end').style.display = 'none';
	}
}})()


APP.modules.preview=(function(){
return{
	previewdocument:function(link){
		$("#preview").empty();
		$("#preview").append(  '<iframe src="'+link+'" style="width:600px; height:500px;" frameborder="0"></iframe>');
		}
		
}})()






$(document).ready(function(){
$('.ui .dropdown')
  .dropdown()
;
	APP.init();
	APP.modules.upload.init();

	APP.modules.search.init();
  	
  		    $(".date").datepicker({dateFormat: "yy-mm-dd"});

  	


});



