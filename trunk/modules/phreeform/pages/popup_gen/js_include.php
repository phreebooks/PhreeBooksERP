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
//  Path: /modules/phreeform/pages/popup_gen/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var theTable, theTableBody;
var textLandscape = '<?php echo TEXT_LANDSCAPE; ?>';
var textPortrait  = '<?php echo TEXT_PORTRAIT; ?>';
var tableInit     = new Array();
var tableCount    = 0;
<?php echo js_calendar_init($cal_from); ?>
<?php echo js_calendar_init($cal_to); ?>

<?php 
  if ($report->reporttype == 'rpt') {
    echo "tableInit[tableCount] = 'field_setup'; tableCount++;" . chr(10);
  } 
?>
<?php echo $jsArray; ?>

function init() {
  for (var tables in tableInit) {
    var table = document.getElementById(tableInit[tables]);
    var tableDnD = new TableDnD();
    tableDnD.init(table);
  }
<?php if ($report->reporttype == 'rpt') echo '  calculateWidth();'; ?>

}

function check_form() {
  var rpt_id = false;
  for (var i = 0; i < document.popup_gen.rID.length; i++) {
	if (document.popup_gen.rID[i].checked) rpt_id = true;
  }
  if (!document.popup_gen.rID.length) {
	if (document.popup_gen.rID.value) rpt_id = true; // for single form entries
  }
  if (!rpt_id) {
	alert('<?php echo PHREEFORM_NO_SELECTION; ?>');
	return false;
  }
  return true;
}

// Insert other page specific functions here.
// ajax pair to fetch email message specific to a given report/form
function fetchEmailMsg() {
  var rID;
  for (var i = 0; i < document.popup_gen.rID.length; i++) {
	if (document.popup_gen.rID[i].checked) rID = document.popup_gen.rID[i].value;
  }
  if (!document.popup_gen.rID.length) {
	if (document.popup_gen.rID.value) rID = document.popup_gen.rID.value; // for single form entries
  }
  if (rID) {
    $.ajax({
      type: "GET",
	  url: 'index.php?module=phreeform&page=ajax&op=load_email_msg&rID='+rID,
      dataType: ($.browser.msie) ? "text" : "xml",
      error: function(XMLHttpRequest, textStatus, errorThrown) {
    	  $.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
      },
	  success: fillEmailMsg
    });
  }
}

function fillEmailMsg(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("subject").text()) document.getElementById('message_subject').value = $(xml).find("subject").text();
  if ($(xml).find("text").text())    document.getElementById('message_body').value    = $(xml).find("text").text();
}

function querySaveAs() {
 	$.messager.prompt('<?php echo TEXT_REPORT?>', "<?php echo PHREEFORM_RPTENTER; ?>", function(title){
		if (title){
			document.getElementById('title').value = title;
		    submitToDo('save_as');
		}
		return false;
	});
}

function hideEmail() {
  for (var i=0; i<document.popup_gen.delivery_method.length; i++) {
    if (document.popup_gen.delivery_method[i].checked) {
      break;
    }
  }
  var deliveryValue = document.popup_gen.delivery_method[i].value;
  if (deliveryValue == 'S') {
    document.getElementById('rpt_email').style.display = 'block';
  } else {
    document.getElementById('rpt_email').style.display = 'none';
  }
}

function calculateWidth() {	// total up the columns
	var brk = new Array();
	var maxColWidth = 0;
	var colWidth, temp;
	var totalWidth = parseFloat(document.getElementById('marginleft').value);
	var colCount   = 1;
	var rowColCnt  = new Array();
	var rowWidth   = new Array();

	for (seq=0; seq<document.getElementById('field_setup').rows.length - 2; seq++) {
      if (document.getElementById('field_setup').rows.length == 3) {
	    cValue = document.forms[0].elements['fld_clmn[]'].value;
		cVis   = document.forms[0].elements['fld_vis[]'].value;
		cBreak = document.forms[0].elements['fld_brk[]'].value;
	  } else {
	    cValue = document.forms[0].elements['fld_clmn[]'][seq].value;
		cVis   = document.forms[0].elements['fld_vis[]'][seq].value;
		cBreak = document.forms[0].elements['fld_brk[]'][seq].value;
	  }
	  if (cValue == '') cValue = '0';
	  colWidth = parseFloat(cValue);
	  if (isNaN(colWidth)) continue;
	  if (cVis == '1') {
		if (colWidth > maxColWidth) {
		  totalWidth += colWidth - maxColWidth;
		  maxColWidth = colWidth;
		  rowWidth[colCount] = totalWidth;
		}
		rowColCnt[seq] = colCount;
		if (cBreak = '1') {
		  colCount++;
		  maxColWidth = 0;
		}
	  } else {
	    rowColCnt[seq] = 0;
	  }
	}
	// set the page information
	for (var i = 0; i < document.forms[0].paperorientation.length; i++) if (document.forms[0].paperorientation[i].checked) break;
	var orientation  = document.forms[0].paperorientation[i].value;
	var orienText    = (orientation == 'P' ? textPortrait : textLandscape);
	var index        = document.getElementById('papersize').selectedIndex;
	var paperValue   = document.getElementById('papersize').options[index].value;
	var marginValues = paperValue.split(':');
	pageWidth = (orientation == 'P') ? marginValues[1] : marginValues[2];
	var pageProperties = '<?php echo PHREEFORM_FLDLIST; ?>';
	pageProperties += ' ('+'<?php echo TEXT_ORIEN; ?>'+': '+orienText;
	pageProperties += ', '+'<?php echo TEXT_WIDTH; ?>'+': '+pageWidth;
	pageProperties += ', '+'<?php echo PHREEFORM_PGMARGIN_L; ?>'+': '+document.getElementById('marginleft').value;
	pageProperties += ', '+'<?php echo PHREEFORM_PGMARGIN_R; ?>'+': '+document.getElementById('marginright').value+')';
	if (document.all) { // IE browsers
	  document.getElementById('fieldListHeading').innerText   = pageProperties;
	} else { //firefox
	  document.getElementById('fieldListHeading').textContent = pageProperties;
	}

	for (var seq = 0; seq < document.getElementById('field_setup').rows.length - 2; seq++) {
	  colCount = rowColCnt[seq];
	  if (colCount != 0) {
	    colWidth = rowWidth[colCount];
	  } else {
	    colWidth = '';
	  }
		if (document.all) { // IE browsers
		  document.getElementById('field_setup').rows[seq+2].cells[4].innerText   = isNaN(colWidth) ? '' : colWidth;
		} else { //firefox
		  document.getElementById('field_setup').rows[seq+2].cells[4].textContent = isNaN(colWidth) ? '' : colWidth;
		}
		document.getElementById('field_setup').rows[seq+2].cells[4].style.color = (colWidth > pageWidth) ? 'red' : '';
	}
}

/**************************************************************************/
/** Keep hold of the current table being dragged */
var currenttable = null;
var rClick       = 0; // stores the row position that was moved 

/** Capture the onmousemove so that we can see if a row from the current
 *  table if any is being dragged.
 * @param ev the event (for Firefox and Safari, otherwise we use window.event for IE)
 */
document.onmousemove = function(ev){
    if (currenttable && currenttable.dragObject) {
        ev   = ev || window.event;
        var mousePos = currenttable.mouseCoords(ev);
        var y = mousePos.y - currenttable.mouseOffset.y;
        if (y != currenttable.oldY) {
            // work out if we're going up or down...
            var movingDown = y > currenttable.oldY;
            // update the old value
            currenttable.oldY = y;
            // update the style to show we're dragging
            currenttable.dragObject.style.backgroundColor = "#eee";
            // If we're over a row then move the dragged row to there so that the user sees the
            // effect dynamically
            var currentRow = currenttable.findDropTargetRow(y);
            if (currentRow) {
                if (movingDown && currenttable.dragObject != currentRow) {
                    currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow.nextSibling);
                } else if (! movingDown && currenttable.dragObject != currentRow) {
                    currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow);
                }
            }
        }

        return false;
    }
}

// Similarly for the mouseup
document.onmouseup   = function(ev){
    if (currenttable && currenttable.dragObject) {
        var droppedRow = currenttable.dragObject;
        // If we have a dragObject, then we need to release it,
        // The row will already have been moved to the right place so we just reset stuff
        droppedRow.style.backgroundColor = 'transparent';
        currenttable.dragObject   = null;
        // And then call the onDrop method in case anyone wants to do any post processing
        currenttable.onDrop(currenttable.table, droppedRow);
        currenttable = null; // let go of the table too
    }
}


/** get the source element from an event in a way that works for IE and Firefox and Safari
 * @param evt the source event for Firefox (but not IE - IE uses window.event) */
function getEventSource(evt) {
    if (window.event) {
        evt = window.event; // For IE
        return evt.srcElement;
    } else {
        return evt.target; // For Firefox
    }
}

/**
 * Encapsulate table Drag and Drop in a class. We'll have this as a Singleton
 * so we don't get scoping problems.
 */
function TableDnD() {
    /** Keep hold of the current drag object if any */
    this.dragObject = null;
    /** The current mouse offset */
    this.mouseOffset = null;
    /** The current table */
    this.table = null;
    /** Remember the old value of Y so that we don't do too much processing */
    this.oldY = 0;

    /** Initialise the drag and drop by capturing mouse move events */
    this.init = function(table) {
        this.table = table;
        var rows = table.tBodies[0].rows; //getElementsByTagName("tr")
        for (var i=0; i<rows.length; i++) {
			// John Tarr: added to ignore rows that I've added the NoDnD attribute to (Category and Header rows)
			var nodrag = rows[i].getAttribute("NoDrag")
			if (nodrag == null || nodrag == "undefined") { //There is no NoDnD attribute on rows I want to drag
				this.makeDraggable(rows[i]);
			}
        }
    }

    /** This function is called when you drop a row, so redefine it in your code
        to do whatever you want, for example use Ajax to update the server */
    this.onDrop = function(table, droppedRow) {
        rClick = droppedRow.rowIndex;
    }

	/** Get the position of an element by going up the DOM tree and adding up all the offsets */
    this.getPosition = function(e){
        var left = 0;
        var top  = 0;
		/** Safari fix - thanks to Luis Chato for this! */

		if (e.offsetHeight == 0) {
			/** Safari 2 doesn't correctly grab the offsetTop of a table row
			    this is detailed here:
			    http://jacob.peargrove.com/blog/2006/technical/table-row-offsettop-bug-in-safari/
			    the solution is likewise noted there, grab the offset of a table cell in the row - the firstChild.
			    note that firefox will return a text node as a first child, so designing a more thorough
			    solution may need to take that into account, for now this seems to work in firefox, safari, ie */
			e = e.firstChild; // a table cell
		}

        while (e.offsetParent){
            left += e.offsetLeft;
            top  += e.offsetTop;
            e     = e.offsetParent;
        }

        left += e.offsetLeft;
        top  += e.offsetTop;

        return {x:left, y:top};
    }

	/** Get the mouse coordinates from the event (allowing for browser differences) */
    this.mouseCoords = function(ev){
        if(ev.pageX || ev.pageY){
            return {x:ev.pageX, y:ev.pageY};
        }
        return {
            x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
            y:ev.clientY + document.body.scrollTop  - document.body.clientTop
        };
    }

	/** Given a target element and a mouse event, get the mouse offset from that element.
		To do this we need the element's position and the mouse position */
    this.getMouseOffset = function(target, ev){
        ev = ev || window.event;

        var docPos    = this.getPosition(target);
        var mousePos  = this.mouseCoords(ev);
        return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
    }

	/** Take an item and add an onmousedown method so that we can make it draggable */
    this.makeDraggable = function(item) {
        if(!item) return;
        var self = this; // Keep the context of the TableDnd inside the function
        item.onmousedown = function(ev) {
            // Need to check to see if we are an input or not, if we are an input, then
            // return true to allow normal processing
            var target = getEventSource(ev);
            if (target.tagName == 'INPUT' || target.tagName == 'SELECT') return true;
            currenttable = self;
            self.dragObject  = this;
            self.mouseOffset = self.getMouseOffset(this, ev);
            return false;
        }
        item.style.cursor = "move";
    }

    /** We're only worried about the y position really, because we can only move rows up and down */
    this.findDropTargetRow = function(y) {
        var rows = this.table.tBodies[0].rows;
		for (var i=0; i<rows.length; i++) {
			var row = rows[i];
			// John Tarr added to ignore rows that I've added the NoDnD attribute to (Header rows)
			var nodrop = row.getAttribute("NoDrop");
			if (nodrop == null || nodrop == "undefined") {  //There is no NoDnD attribute on rows I want to drag
				var rowY    = this.getPosition(row).y;
				var rowHeight = parseInt(row.offsetHeight)/2;
				if (row.offsetHeight == 0) {
					rowY = this.getPosition(row.firstChild).y;
					rowHeight = parseInt(row.firstChild.offsetHeight)/2;
				}
				// Because we always have to insert before, we need to offset the height a bit
				if ((y > rowY - rowHeight) && (y < (rowY + rowHeight))) {
					// that's the row we're over
					return row;
				}
			}
		}
		return null;
	}
}

// -->
</script>
