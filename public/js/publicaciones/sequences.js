function sequences(dataURL,flag,activation, colors){
	function angle(d) {
		var a = (d.startAngle + d.endAngle) * 90 / Math.PI - 90;
		return a > 90 ? a - 180 : a;
	}
		  
	// Dimensions of sunburst.
	var width = 600;
	var height = 600;
	var radius = Math.min(width, height) / 2;

	// Breadcrumb dimensions: width, height, spacing, width of tip/tail.
	var b = {
	  w: 100, h: 30, s: 3, t: 10
	};

	// Mapping of step names to colors.
	var legendColors = {
		"NATURAL SCIENCES" : "#00AA00",
		"ENGINEERING AND TECHNOLOGY" : "#0000FF",
		"MEDICAL AND HEALTH SCIENCES" : "#00BBBB",
		"AGRICULTURAL SCIENCES" : "#CCAA00",
		"SOCIAL SCIENCES" : "#EE6600",
		"HUMANITIES" : "#FF00AA"
	}


	// Total size of all segments; we set this later, after loading the data.
	var vis = d3.select("#chart")
	var totalSize = 0; 
	
	

	var partition = d3.layout.partition()
		.size([2 * Math.PI, radius * radius])
		.value(function(d) { return d.size; });

	var arc = d3.svg.arc()
		.startAngle(function(d) { return d.x; })
		.endAngle(function(d) { return d.x + d.dx; })
		.innerRadius(function(d) { return Math.sqrt(d.y); })
		.outerRadius(function(d) { return Math.sqrt(d.y + d.dy); });

	// Use d3.text and d3.csv.parseRows so that we do not need to have a header
	// row, and can receive the csv as an array of arrays.
	
	
	d3.text(dataURL, function(text) {
	  var csv = d3.csv.parseRows(text);
	  var json = buildHierarchy(csv);
	  createVisualization(json);
	});

	function createVisualization(json) {
	  $("svg").remove();
	  var vis = d3.select("#chart").append("svg:svg")
		.attr("width", width)
		.attr("height", height)
		.append("svg:g")
		.attr("id", "container")	
		.on("click",function(){
			  if(activation == 0) activation = 1
			  else activation = 0
		   })		
		.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
	  // Basic setup of page elements.
	  initializeBreadcrumbTrail();
	  //~ d3.select("#togglelegend").on("click", toggleLegend);

	  // Bounding circle underneath the sunburst, to make it easier to detect
	  // when the mouse leaves the parent g.
	  vis.append("svg:circle")
		  .attr("r", radius)
		  .style("opacity", 0);

	  // For efficiency, filter nodes to keep only those large enough to see.
	  var nodes = partition.nodes(json)
		  .filter(function(d) {
		  return (d.dx > 0.005); // 0.005 radians = 0.29 degrees
		  });

	  var path = vis.data([json]).selectAll("path")
		  .data(nodes)
		  .enter().append("svg:g")
		  .append("svg:path")
		  .attr("id",function(d){return d.name})
		  .attr("display", function(d) { return d.depth ? null : "none"; })
		  .attr("d", arc)
		  .attr("class", function(d) { return "ring_" + d.depth; })
		  .attr("fill-rule", "evenodd")
		  .style("fill", function(d) { return d.children ? (colors[0][d.name] ? colors[0][d.name] : get_random_gray()) : "#b2b2b2"; })
		  .style("opacity", 1)		   
		  .on("mouseover", mouseover);
	  var g = vis.selectAll("g")
			.append("svg:text")
			  .attr("dy", ".35em")
			  .attr("text-anchor", "middle")
			  .attr("fill","white")
			  .attr("transform", function(d) { return "translate(" + arc.centroid(d) + ")"; })
			  .text(function(d) {if(d.depth == 1) return d.name.substr(0, 1) });
		  g = vis.selectAll("g")
		  //~ g.append("svg:title")
			//~ .text(function(d){return d.cited+"-"+d.size;});
		
	  // Add the mouseleave handler to the bounding circle.
	  d3.select("#container").on("mouseleave", function(d){if(activation == 1) mouseleave(d)});

	  // Get total size of the tree = value of root node from partition.
	  totalSize = path.node().__data__.value;
	 };

	// Fade all but the current sequence, and show it in the breadcrumb trail.
	function mouseover(d) {
	  if (activation == 1){
		  $("#list").empty()
		  if (flag == 1) art = "publicaciones"
		  if (flag == 2) art = "citas"
		  $("#list").append("<div id='list-table' style='display:table;'></div>")
		  try{
			  for(var i = 0;i < d.children.length;i++){
					$("#list-table").append("<div id='list-row"+i+"' style='display:table-row;'></div>")
					$("#list-row"+i).append("<li type='disc' style='width:10px;'></li>")
					$("#list-row"+i).append("<div style='display:table-cell;width:145px;'>"+upper(((d.children[i].name).replace(/;/g,",")))+"</div>")
					if(d.children[i].children || d.children[i].name == 'Q1' || d.children[i].name == 'Q2' || d.children[i].name == 'Q3' || d.children[i].name == 'Q4' || d.children[i].name == 'CUARTIL NO DEFINIDO'){
						$("#list-row"+i).append("<div style='display:table-cell;width:5px'>: </div>")	
						$("#list-row"+i).append("<div style='color:red;display:table-cell;width:40px;'>"+(100*d.children[i].value/d.children[i].parent.value).toFixed(2)+"%</div>")
						$("#list-row"+i).append("<div style='color:grey;display:table-cell;width:100px;'> ("+d.children[i].value+" "+art+")</div>")
					}
			  }
		  }catch(e){
		  
		  }
		  var percentage = (100 * d.value / totalSize).toPrecision(3);
		  var percentageString = percentage + "%";
		  if (percentage < 0.1) {
			percentageString = "< 0.1%";
		  }
		  var percentage2 = (100*d.value/d.parent.value).toPrecision(3)
		  var percentageString2 = percentage2 + "%";
		  d3.select("#percentage2")
			  .text(percentageString);
		  d3.select("#percentage")
			  .text(percentageString2);
		  d3.select("#explanation")
			  .style("visibility", "");
		  d3.select("#explanation2")
			  .style("visibility", "");
		  var sequenceArray = getAncestors(d);
		  updateBreadcrumbs(sequenceArray);

		  // Fade all the segments.
		  d3.selectAll("path")
			  .style("opacity", 0.3);

		  // Then highlight only those that are an ancestor of the current segment.
		  vis.selectAll("path")
			  .filter(function(node) {
						return (sequenceArray.indexOf(node) >= 0);
					  })
			  .style("opacity", 1);
		  }
	}

	// Restore everything to full opacity when moving off the visualization.
	function mouseleave(d) {
	  if (activation == 1){
		  // Hide the breadcrumb trail
		  d3.select("#trail")
			  .style("visibility", "hidden");

		  // Deactivate all segments during transition.
		  d3.selectAll("path").on("mouseover", null);

		  // Transition each segment to full opacity and then reactivate it.
		  d3.selectAll("path")
			  .transition()
			  .duration(0)
			  .style("opacity", 1)
			  .each("end", function() {
					  d3.select(this).on("mouseover", mouseover);
					});

		  d3.select("#explanation")
			  .transition()
			  .duration(0)
			  .style("visibility", "hidden");

		  d3.select("#explanation2")
			  .transition()
			  .duration(0)
			  .style("visibility", "hidden");
		  }
	}
	// Given a node in a partition layout, return an array of all of its ancestor
	// nodes, highest first, but excluding the root.
	function getAncestors(node) {
	  var path = [];
	  var current = node;
	  while (current.parent) {
		path.unshift(current);
		current = current.parent;
	  }
	  return path;
	}

	function initializeBreadcrumbTrail() {
	  // Add the svg area.
	  var trail = d3.select("#sequence").append("svg:svg")
		  .attr("width", "100%")
		  .attr("height", 50)
		  .attr("id", "trail");
	  // Add the label at the end, for the percentage.
	  trail.append("svg:text")
		.attr("id", "endlabel")
		.style("fill", "#000");
	}

	// Generate a string that describes the points of a breadcrumb polygon.
	function breadcrumbPoints(d, i) {
	  if (d.depth == 1){
		b.w = 270;
	  }else
		b.w = 300;
	  var points = [];
	  points.push("0,0");
	  points.push(b.w + ",0");
	  points.push(b.w + b.t + "," + (b.h / 2));
	  points.push(b.w + "," + b.h);
	  points.push("0," + b.h);
	  if (i > 0) { // Leftmost breadcrumb; don't include 6th vertex.
		points.push(b.t + "," + (b.h / 2));
	  }
	  return points.join(" ");
	}

	function get_random_color() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.round(Math.random() * 15)];
    }
    return color;
	}

	function get_random_gray(){
		var value = Math.random() * 0xFF | 0;
		var grayscale = (value << 16) | (value << 8) | value;
		var color = '#' + grayscale.toString(16);	
		return color;		
	}

	// Update the breadcrumb trail to show the current sequence and percentage.
	function updateBreadcrumbs(nodeArray) { 
	  // Data join; key function combines name and depth (= position in sequence).

	  var g = d3.select("#trail")
		  .selectAll("g")
		  .data(nodeArray, function(d) { return d.name + d.depth});
	  // Add breadcrumb and label for entering nodes.
	  var entering = g.enter().append("svg:g");

	  entering.append("svg:polygon")
		  .attr("points", breadcrumbPoints)
		  .style("fill", function(d) { return d.children ? (colors[0][d.name] ? colors[0][d.name] : get_random_gray()) : "#b2b2b2"; });

	  entering.append("svg:text")
		  .attr("x", function(d){
			   if (d.depth == 1){
				b.w = 240;
			  }
			  else if (2 == d.depth){
				b.w = 50;
			  }
			    else
				b.w = 200;
			  return (b.w + b.t) / 2})
		  .attr("y", b.h / 2)
		  .attr("dy", "0.35em")
		  .attr("text-anchor", "middle")
		  .text(function(d) { return d.size ? upper((d.name).toLowerCase().substr(0, 35) + "...") : upper((d.name).toLowerCase()) });

	  // Set position for entering and updating nodes.
	  g.attr("transform", function(d, i) {	 
		if (d.depth == 1)
			return "translate(" + i * (b.w + b.s) + ", 0)";
		if (d.depth == 2)
			return "translate(" + (240 + i*b.s) + ", 0)";
		if (d.depth == 3)
			return "translate(" + (90+200 + i*b.s) + ", 0)";
		if (d.depth == 4)
			return "translate(" + (90+400 + i*b.s) + ", 0)";
	  });

	  // Remove exiting nodes.
	  g.exit().remove();

	  // Make the breadcrumb trail visible, if it's hidden.
	  d3.select("#trail")
		  .style("visibility", "");

	}


	function buildHierarchy(csv) {
	  var root = {"name": "root", "children": []};
	  for (var i = 0; i < csv.length; i++) {
		var sequence = csv[i][0];
		if (flag == 1)
			var size = +csv[i][1];
		if (flag == 2)
			var size = +csv[i][2];
		if (isNaN(size)) { // e.g. if this is a header row
		  continue;
		}
		var parts = sequence.split("-");
		var currentNode = root;
		for (var j = 0; j < parts.length; j++) {
		  var children = currentNode["children"];
		  var nodeName = parts[j];
		  var childNode;
		  if (j + 1 < parts.length) {
	   // Not yet at the end of the sequence; move down the tree.
		var foundChild = false;
		for (var k = 0; k < children.length; k++) {
		  if (children[k]["name"] == nodeName) {
			childNode = children[k];
			foundChild = true;
			break;
		  }
		}
	  // If we don't already have a child node for this branch, create it.
		if (!foundChild) {
		  childNode = {"name": nodeName, "children": []};
		  children.push(childNode);
		}
		currentNode = childNode;
		  } else {
		// Reached the end of the sequence; create a leaf node.
		childNode = {"name": nodeName, "size": size};
		children.push(childNode);
		  }
		}
	  }
	  return root;
	};
	return activation
}
