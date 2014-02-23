<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/doc_ctl/pages/main/js_include.php
//

?>
<script type="text/javascript" src="<?php echo DIR_WS_ADMIN; ?>modules/doc_ctl/includes/jstree/_lib/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo DIR_WS_ADMIN; ?>modules/doc_ctl/includes/jstree/_lib/jquery.hotkeys.js"></script>
<script type="text/javascript" src="<?php echo DIR_WS_ADMIN; ?>modules/doc_ctl/includes/jstree/jquery.jstree.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo DIR_WS_ADMIN; ?>modules/doc_ctl/includes/jstree/_docs/syntax/!style.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo DIR_WS_ADMIN; ?>modules/doc_ctl/includes/jstree/_docs/!style.css"/>
<script type="text/javascript" src="<?php echo DIR_WS_ADMIN; ?>modules/doc_ctl/includes/jstree/_docs/syntax/!script.js"></script>
<script type="text/javascript">
<!--
// required function called with every page load
function init() {
<!-- JavaScript necessary for the tree -->
  $(function () {
    $("#demo")
	  .bind("before.jstree", function (e, data) {
		$("#alog").append(data.func + "<br />");
	  })
	  .jstree({ // List of active plugins
		"plugins" : [ 
			"themes","json_data","ui","crrm","cookies","dnd","search","types","hotkeys","contextmenu" 
		],
		"json_data" : { 
			"ajax" : {
				"url" : "index.php?module=doc_ctl&page=ajax&op=jstree_operation",
				"data" : function (n) { 
					return { 
						"operation" : "get_children", 
						"id" : n.attr ? n.attr("id").replace("node_","") : 1 
					}; 
				}
			}
		},
		"search" : {
			"ajax" : {
				"url" : "index.php?module=doc_ctl&page=ajax&op=jstree_operation",
				"data" : function (str) {
					return { 
						"operation" : "search", 
						"search_str" : str 
					}; 
				}
			}
		},
		"types" : {
			"max_depth" : -2,
			"max_children" : -2,
			"valid_children" : [ "drive" ],
			"types" : {
				"default" : {
					"valid_children" : "none",
					"icon" : {
						"image" : "<?php echo DIR_WS_ICONS; ?>16x16/actions/format-justify-fill.png"
					}
				},
				"folder" : {
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "<?php echo DIR_WS_ICONS; ?>16x16/status/folder-open.png"
					}
				},
				"drive" : {
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "<?php echo DIR_WS_ICONS; ?>16x16/devices/drive-harddisk.png"
					},
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"remove" : false
				}
			}
		},
		"ui" : {
			"initially_select" : [ "node_4" ]
		},
		"core" : { 
			"initially_open" : [ "node_2" , "node_3" ] 
		}
	})
	.bind("create.jstree", function (e, data) {
		$.post(
			"index.php?module=doc_ctl&page=ajax&op=jstree_operation", 
			{ 
				"operation" : "create_node", 
				"id" : data.rslt.parent.attr("id").replace("node_",""), 
				"position" : data.rslt.position,
				"title" : data.rslt.name,
				"type" : data.rslt.obj.attr("rel")
			}, 
			function (r) {
				if(r.status) {
					$(data.rslt.obj).attr("id", "node_" + r.id);
				}
				else {
					$.jstree.rollback(data.rlbk);
				}
			}
		);
	})
	.bind("remove.jstree", function (e, data) {
		data.rslt.obj.each(function () {
			$.ajax({
				async : false,
				type: 'POST',
				url: "index.php?module=doc_ctl&page=ajax&op=jstree_operation",
				data : { 
					"operation" : "remove_node", 
					"id" : this.id.replace("node_","")
				}, 
				success : function (r) {
					if(!r.status) {
						data.inst.refresh();
					}
				}
			});
		});
	})
	.bind("rename.jstree", function (e, data) {
		$.post(
			"index.php?module=doc_ctl&page=ajax&op=jstree_operation", 
			{ 
				"operation" : "rename_node", 
				"id" : data.rslt.obj.attr("id").replace("node_",""),
				"title" : data.rslt.new_name
			}, 
			function (r) {
				if(!r.status) {
					$.jstree.rollback(data.rlbk);
				}
			}
		);
	})
    .bind("select_node.jstree", function (event, data) {
       fetch_doc(data.rslt.obj.attr("id").replace("node_",""));
    })
	.bind("move_node.jstree", function (e, data) {
		data.rslt.o.each(function (i) {
			$.ajax({
				async : false,
				type: 'POST',
				url: "index.php?module=doc_ctl&page=ajax&op=jstree_operation",
				data : { 
					"operation" : "move_node", 
					"id" : $(this).attr("id").replace("node_",""), 
					"ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""), 
					"position" : data.rslt.cp + i,
					"title" : data.rslt.name,
					"copy" : data.rslt.cy ? 1 : 0
				},
				success : function (r) {
					if(!r.status) {
						$.jstree.rollback(data.rlbk);
					}
					else {
						$(data.rslt.oc).attr("id", "node_" + r.id);
						if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
							data.inst.refresh(data.inst._get_parent(data.rslt.oc));
						}
					}
					$("#analyze").click();
				}
			});
		});
	});
  });
  // Code for the menu buttons
  $(function () { 
	$("#mmenu img").click(function () {
		switch(this.id) {
			case "add_default":
			case "add_folder":
				$("#demo").jstree("create", null, "last", { "attr" : { "rel" : this.id.toString().replace("add_", "") } });
				break;
			case "search":
				$("#demo").jstree("search", document.getElementById("text").value);
				break;
			case "text": break;
			default:
				$("#demo").jstree(this.id);
				break;
		}
	});
  });
}

// required function called with every form submit. return true on success
function check_form() {
	return true;
}

function boxShow(id) {
	document.getElementById(id).style.display = '';
	document.getElementById('up_arrow').style.display = '';
	document.getElementById('down_arrow').style.display = 'none';
}

function boxHide(id) {
	document.getElementById(id).style.display = 'none';
	document.getElementById('up_arrow').style.display = 'none';
	document.getElementById('down_arrow').style.display = '';
}

// ajax call to get center column details
function fetch_home() {
  fetch_doc(-1);
}

function fetch_doc(id) {
//  document.getElementById("id").value = id;
  if (!id) return;
  $.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
	url: 'index.php?module=doc_ctl&page=ajax&op=load_doc_details&id='+id,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fillDocDetails
  });
}

function fillDocDetails(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  obj = document.getElementById("rightColumn");
  obj.innerHTML = $(xml).find("htmlContents").text();
}

// misc functions for action processing
function docAction(action) {
  var id = document.getElementById("id").value;
  if (!id) return;
  $.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
	url: 'index.php?module=doc_ctl&page=ajax&op=doc_operation&action='+action+'&id='+id,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: docActionResp
  });
}

function docActionResp(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("msg").text()) $.messager.alert("Info",$(xml).find("msg").text(),"info"); 
  fetch_doc($(xml).find("docID").text());
  if ($(xml).find("action").text() == 'reload_tree') $('#demo').jstree('refresh',-1);
}

function deleteBookmark(id) {
  $.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    url: 'index.php?module=doc_ctl&page=ajax&op=doc_operation&fID=rcvBkMkDel&action=del_bookmark&id='+id,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
    success: respDelBookmark
  });
}

function respDelBookmark(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("msg").text()) $.messager.alert("Info",$(xml).find("msg").text(),"info"); 
  location.reload(true);
}

// -->
</script>
