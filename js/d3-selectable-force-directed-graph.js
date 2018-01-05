function createSelectableForceDirectedGraph(svg, graph) {
    var width = +svg.attr("width"),
        height = +svg.attr("height");

    let parentWidth = d3.select('svg').node().parentNode.clientWidth;
    let parentHeight = d3.select('svg').node().parentNode.clientHeight;

    var svg = d3.select('svg')
    .attr('width', parentWidth)
    .attr('height', parentHeight)

    // remove any previous graphs
    svg.selectAll('.g-main').remove();
	svg.selectAll('text').remove();

    var gMain = svg.append('g')
    .classed('g-main', true);

    var rect = gMain.append('rect')
    .attr('width', parentWidth)
    .attr('height', parentHeight)
    .style('fill', 'white')

    var gDraw = gMain.append('g');

    var zoom = d3.zoom()
    .on('zoom', zoomed)

    gMain.call(zoom);


    function zoomed() {
        gDraw.attr('transform', d3.event.transform);
    }

    var color = d3.scaleOrdinal(d3.schemeCategory20);

    if (! ("links" in graph)) {
        console.log("Graph is missing links");
        return;
    }

    var nodes = {};
    var i;
    for (i = 0; i < graph.nodes.length; i++) {
        nodes[graph.nodes[i].id] = graph.nodes[i];
        graph.nodes[i].weight = 1.01;
    }

    // the brush needs to go before the nodes so that it doesn't
    // get called when the mouse is over a node
    var gBrushHolder = gDraw.append('g');
    var gBrush = null;
	
	gDraw.append("defs").selectAll("marker")
		.data(["end"])
		.enter().append("marker")
		.attr("id", function(d) { return d; })
		.attr("viewBox", "0 -5 10 10")
		.attr("refX", 25)
		.attr("refY", 0)
		.attr("markerWidth", 6)
		.attr("markerHeight", 6)
		.attr("orient", "auto")
		.append("path")
		.attr("d", "M0,-5L10,0L0,5 L10,0 L0, -5")
		.style("stroke", "#4679BD")
		.style("opacity", "0.6");

    var link = gDraw.append("g")
        .attr("class", "link")
        .selectAll("line")
        .data(graph.links)
        .enter().append("line")
        .attr("stroke-width", 1)//function(d) { return Math.sqrt(d.value); });
		.style("marker-end",  "url(#end)"); // Modified line
	
	var colorId = -1;
    var node = gDraw.append("g")
        .attr("class", "node")
        .selectAll("circle")
        .data(graph.nodes)
        .enter().append("g")
		.attr("class", "grp-node")
		.append("circle")
        .attr("r", 5)
        .attr("fill", function(d) {
			if (colorId < 19)
				colorId += 1;
			else {
				colorId = 0;
			}
			return color(colorId);
		})/*function(d) { 
            if ('color' in d)
                return d.color;
            else
                return color(d.group); 
        })*/
        .call(d3.drag()
        .on("start", dragstarted)
        .on("drag", dragged)
        .on("end", dragended));

      
    // add titles for mouseover blurbs
    //node
	var textnode = d3.selectAll(".grp-node").append("text")
		.attr("x", function() {
			return d3.select(this.parentNode.childNodes[0]).attr("cx") + 5;
		})
		.attr("y", function() {
			return d3.select(this.parentNode.childNodes[0]).attr("cy") + 3;
		})
        .text(function(d) { 
            if ('name' in d)
                return d.name;
            else
                return d.id; 
        });

    var simulation = d3.forceSimulation()
        .force("link", d3.forceLink()
                .id(function(d) { return d.id; })
                .distance(function(d) { 
                    return 400;
                    //var dist = 20 / d.value;
                    //console.log('dist:', dist);

                    //return dist; 
                })
              )
        .force("charge", d3.forceManyBody())
        .force("center", d3.forceCenter(parentWidth / 2, parentHeight / 2))
        .force("x", d3.forceX(parentWidth/2))
        .force("y", d3.forceY(parentHeight/2));

    simulation
        .nodes(graph.nodes)
        .on("tick", ticked);

    simulation.force("link")
        .links(graph.links);

    function ticked() {
        // update node and line positions at every step of 
        // the force simulation
        link.attr("x1", function(d) { return d.source.x; })
            .attr("y1", function(d) { return d.source.y; })
            .attr("x2", function(d) { return d.target.x; })
            .attr("y2", function(d) { return d.target.y; });

        node.attr("cx", function(d) { return d.x; })
            .attr("cy", function(d) { return d.y; });
		textnode.attr("x", function(d) { return d.x + 5; })
				.attr("y", function(d) { return d.y + 3; });
    }

    var brushMode = false;
    var brushing = false;

    var brush = d3.brush()
        .on("start", brushstarted)
        .on("brush", brushed)
        .on("end", brushended);

    function brushstarted() {
        // keep track of whether we're actively brushing so that we
        // don't remove the brush on keyup in the middle of a selection
        brushing = true;

        node.each(function(d) { 
            d.previouslySelected = shiftKey && d.selected; 
        });
    }

    rect.on('click', () => {
        node.each(function(d) {
            d.selected = false;
            d.previouslySelected = false;
        });
        node.classed("selected", false);
    });

    function brushed() {
        if (!d3.event.sourceEvent) return;
        if (!d3.event.selection) return;

        var extent = d3.event.selection;

        node.classed("selected", function(d) {
            return d.selected = d.previouslySelected ^
            (extent[0][0] <= d.x && d.x < extent[1][0]
             && extent[0][1] <= d.y && d.y < extent[1][1]);
        });
    }

    function brushended() {
        if (!d3.event.sourceEvent) return;
        if (!d3.event.selection) return;
        if (!gBrush) return;

        gBrush.call(brush.move, null);

        if (!brushMode) {
            // the shift key has been release before we ended our brushing
            gBrush.remove();
            gBrush = null;
        }

        brushing = false;
    }

    d3.select('body').on('keydown', keydown);
    d3.select('body').on('keyup', keyup);

    var shiftKey;

    function keydown() {
        shiftKey = d3.event.shiftKey;

        if (shiftKey) {
            // if we already have a brush, don't do anything
            if (gBrush)
                return;

            brushMode = true;

            if (!gBrush) {
                gBrush = gBrushHolder.append('g');
                gBrush.call(brush);
            }
        }
    }

    function keyup() {
        shiftKey = false;
        brushMode = false;

        if (!gBrush)
            return;

        if (!brushing) {
            // only remove the brush if we're not actively brushing
            // otherwise it'll be removed when the brushing ends
            gBrush.remove();
            gBrush = null;
        }
    }

    function dragstarted(d) {
      if (!d3.event.active) simulation.alphaTarget(0.9).restart();

        if (!d.selected && !shiftKey) {
            // if this node isn't selected, then we have to unselect every other node
            node.classed("selected", function(p) { return p.selected =  p.previouslySelected = false; });
        }

        d3.select(this).classed("selected", function(p) { d.previouslySelected = d.selected; return d.selected = true; });

        node.filter(function(d) { return d.selected; })
        .each(function(d) { //d.fixed |= 2; 
          d.fx = d.x;
          d.fy = d.y;
		  d3.select(this.parentNode.childNodes[1]).attr("x", d.fx).attr("y", d.fy);
        })

    }

    function dragged(d) {
      //d.fx = d3.event.x;
      //d.fy = d3.event.y;
		node.filter(function(d) { return d.selected; })
		.each(function(d) { 
			d.fx += d3.event.dx;
			d.fy += d3.event.dy;
			d3.select(this.parentNode.childNodes[1]).attr("x", d.fx).attr("y", d.fy);
		})
    }

    function dragended(d) {
      if (!d3.event.active) simulation.alphaTarget(0);
      d.fx = null;
      d.fy = null;
        node.filter(function(d) { return d.selected; })
        .each(function(d) { //d.fixed &= ~6; 
            d.fx = null;
            d.fy = null;
			d3.select(this.parentNode.childNodes[1]).attr("x", d.fx).attr("y", d.fy);
        })
    }

    var texts = ['Use the scroll wheel to zoom',
                 'Hold the shift key to select nodes']

    svg.selectAll('text')
        .data(texts, function(d) {return d;})
        .enter()
        .append('text')
        .attr('x', 960)
        .attr('y', function(d,i) { return 550 + i * 18; })
        .text(function(d) { return d; });

    return graph;
};