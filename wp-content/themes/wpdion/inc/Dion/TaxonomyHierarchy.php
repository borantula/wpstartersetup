<?php
/**
 * Created by PhpStorm.
 * User: borayalcin
 * Date: 22/10/14
 * Time: 13:41
 */

namespace Dion;


class TaxonomyHierarchy {


    public $termId;

    public $taxonomy = 'category';

    public $hierachical;

    public $tree = array(
        'current'  => false,
        'children' => false,
        'parent'   => false,
    );

    public $hasParent = false;
    public $hasChildren = false;

    public $childArgs = array();

    public function __construct($termId,$taxonomy = 'category', $childArgs = array())
    {
        $termId = intval($termId);
        if (is_integer($termId)) {
            $this->termId = $termId;
        } else {
            return new WP_Error('no post id', __("Post ID must be an integer", "dion"));
        }

        $this->taxonomy = $taxonomy;

        //additional wpquery args
        $this->childArgs = $childArgs;

        $this->hierachical = is_taxonomy_hierarchical($this->taxonomy);

        if ($this->hierachical) {
            $this->buildTheTree();
        }
    }


    public function buildTheTree()
    {
        $this->tree['current'] = get_term($this->termId,$this->taxonomy);

        //can be overwritten from constructor
        $args = array(
            'parent'      => $this->termId,
            'hide_empty'    => false,
            'order_by'       => 'menu_order',
            'order' => 'ASC',
        );

        $args = array_merge($args, $this->childArgs);

        //check if it has children
        $children = get_terms($this->taxonomy,$args);


        if ($children) {
            $this->tree['children'] = $children;
            $this->hasChildren      = true;
        }

        /*
        $parent = get_term($this->taxonomy,array('child_of' => $this->));
        if ($parent) {
            $this->tree['parent'] = $parent;
            $this->hasParent      = true;
        }
        */


    }


} 