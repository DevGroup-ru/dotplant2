/**
 * Creates an XPath from a node (currently not used inside this Class (instead FormHTML.prototype.generateName is used) but will be in future);
 * @param  {string=} root   if absent the root is #document
 * @return {string}                 XPath
 * @author https://gist.github.com/MartijnR/5118908
 * @author b37hr3z3n@gmail.com
 */
$.fn.getXPath = function(root){
    //other nodes may have the same XPath but because this function is used to determine the corresponding input name of a data node, index is not included 
    var position,
        $node = this.first(),
        nodeName = $node.prop('nodeName'),
        $sibSameNameAndSelf = $node.siblings(nodeName).addBack(),
        steps = [], 
        $parent = $node.parent(),
        parentName = $parent.prop('nodeName');

    if (typeof(root) === 'object')
        if (typeof(root.length) !== 'undefined')
            root = root[0];

    position = ($sibSameNameAndSelf.length > 1) ? '['+($sibSameNameAndSelf.index($node)+1)+']' : '';
    steps.push(nodeName+position);

    while ($parent.length == 1 && $parent[0] !== root && parentName !== '#document'){
        $sibSameNameAndSelf = $parent.siblings(parentName).addBack();
        position = ($sibSameNameAndSelf.length > 1) ? '['+($sibSameNameAndSelf.index($parent)+1)+']' : '';
        steps.push(parentName+position);
        $parent = $parent.parent();
        parentName = $parent.prop('nodeName');
    }
    return steps.reverse().join('/');
};

$.fn.getXPathWithNodes = function(root) {
    //other nodes may have the same XPath but because this function is used to determine the corresponding input name of a data node, index is not included 
    var position,
        $node = this.first(),
        nodeName = $node.prop('nodeName'),
        $sibSameNameAndSelf = $node.siblings(nodeName).addBack(),
        steps = [], 
        nodes = [],
        $parent = $node.parent(),
        parentName = $parent.prop('nodeName');

    if (typeof(root) === 'object')
        if (typeof(root.length) !== 'undefined')
            root = root[0];

    position = ($sibSameNameAndSelf.length > 1) ? '['+($sibSameNameAndSelf.index($node)+1)+']' : '';
    steps.push(nodeName+position);
    nodes.push($node);

    while ($parent.length == 1 && $parent[0] !== root && parentName !== '#document'){
        $sibSameNameAndSelf = $parent.siblings(parentName).addBack();
        position = ($sibSameNameAndSelf.length > 1) ? '['+($sibSameNameAndSelf.index($parent)+1)+']' : '';
        steps.push(parentName+position);
        nodes.push($parent);
        $parent = $parent.parent();
        parentName = $parent.prop('nodeName');
    }
    return {
        "xpath": steps.reverse().join('/'),
        "nodes": nodes.reverse()
    };

};

/**
 * Get element by XPath and container
 * @author FireBug authors
 * @use XPath.getByXPath($('.work-area .col-md-8').getXPath($('.work-area')), $('.work-area'))
 */

var XPath = {};

XPath.getByXPath = function(xpath, container) {
    return XPath.evaluateXPath(document, xpath, container);
};

// ********************************************************************************************* //
// XPATH

/**
 * Gets an XPath for an element which describes its hierarchical location.
 */
/**
 * Evaluates an XPath expression.
 *
 * @param {Document} doc
 * @param {String} xpath The XPath expression.
 * @param {Node} contextNode The context node.
 * @param {int} resultType
 *
 * @returns {*} The result of the XPath expression, depending on resultType :<br> <ul>
 *          <li>if it is XPathResult.NUMBER_TYPE, then it returns a Number</li>
 *          <li>if it is XPathResult.STRING_TYPE, then it returns a String</li>
 *          <li>if it is XPathResult.BOOLEAN_TYPE, then it returns a boolean</li>
 *          <li>if it is XPathResult.UNORDERED_NODE_ITERATOR_TYPE
 *              or XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, then it returns an array of nodes</li>
 *          <li>if it is XPathResult.ORDERED_NODE_SNAPSHOT_TYPE
 *              or XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, then it returns an array of nodes</li>
 *          <li>if it is XPathResult.ANY_UNORDERED_NODE_TYPE
 *              or XPathResult.FIRST_ORDERED_NODE_TYPE, then it returns a single node</li>
 *          </ul>
 */
XPath.evaluateXPath = function(doc, xpath, contextNode, resultType)
{
    if (contextNode === undefined)
        contextNode = doc;

    if (resultType === undefined)
        resultType = XPathResult.ANY_TYPE;

    if (typeof(contextNode.length) !== 'undefined')
        contextNode = contextNode[0];

    var result = doc.evaluate(xpath, contextNode, null, resultType, null);

    switch (result.resultType)
    {
        case XPathResult.NUMBER_TYPE:
            return result.numberValue;

        case XPathResult.STRING_TYPE:
            return result.stringValue;

        case XPathResult.BOOLEAN_TYPE:
            return result.booleanValue;

        case XPathResult.UNORDERED_NODE_ITERATOR_TYPE:
        case XPathResult.ORDERED_NODE_ITERATOR_TYPE:
            var nodes = [];
            for (var item = result.iterateNext(); item; item = result.iterateNext())
                nodes.push(item);
            return nodes;

        case XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE:
        case XPathResult.ORDERED_NODE_SNAPSHOT_TYPE:
            var nodes = [];
            for (var i = 0; i < result.snapshotLength; ++i)
                nodes.push(result.snapshotItem(i));
            return nodes;

        case XPathResult.ANY_UNORDERED_NODE_TYPE:
        case XPathResult.FIRST_ORDERED_NODE_TYPE:
            return result.singleNodeValue;
    }
};
