<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Chooser for "Product Link" Cms Widget Plugin
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Tigren\Bannermanager\Block\Adminhtml\Block\Widget;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Chooser extends Extended
{
    /**
     * @var array
     */
    protected $_selectedBlocks = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_resourceBlock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magento\Backend\Helper\Data $backendHelper,
        \Tigren\Bannermanager\Model\ResourceModel\Block\CollectionFactory $collectionFactory,
        \Tigren\Bannermanager\Model\ResourceModel\Block $resourceBlock,
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_stdTimezone = $_stdTimezone;
        $this->_resourceBlock = $resourceBlock;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $element->setData('after_element_html', $this->_getAfterElementHtml($element));
        return $element;
    }

    public function _getAfterElementHtml($element)
    {
        $html = <<<HTML
    <style>
         .control .control-value {
            display: none !important;
        }
    </style>
HTML;

        $chooserHtml = $this->getLayout()
            ->createBlock('Tigren\Bannermanager\Block\Adminhtml\Block\Widget\ChooserJs')
            ->setElement($element);

        $html .= $chooserHtml->toHtml();

        return $html;
    }


    public function getCheckboxCheckCallback()
    {
        return "function (grid, element) {
                $(grid.containerId).fire('blocks:changed', {element: element});
            }";
    }


    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_blocks') {
            $selected = $this->getSelectedBlocks();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('block_id', ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter('block_id', ['nin' => $selected]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare products collection, defined collection filters (category, product type)
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $dateTimeNow = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('from_date', [['to' => $dateTimeNow], ['from_date', 'null' => '']])
            ->addFieldToFilter('to_date', [['gteq' => $dateTimeNow], ['to_date', 'null' => '']]);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for products grid
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_blocks',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_blocks',
                'inline_css' => 'checkbox entities',
                'field_name' => 'in_blocks',
                'values' => $this->getSelectedBlocks(),
                'align' => 'center',
                'index' => 'block_id',
                'use_index' => true
            ]
        );

        $this->addColumn(
            'block_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'block_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'block_title',
            [
                'header' => __('Title'),
                'index' => 'block_title',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title',

            ]
        );


        return parent::_prepareColumns();
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'bannersmanager/block_widget/chooser',
            [
                '_current' => true,
                'uniq_id' => $this->getId(),
            ]
        );
    }

    /**
     * Setter
     *
     * @param array $selectedProducts
     * @return $this
     */
    public function setSelectedBlocks($selectedBlocks)
    {
        $this->_selectedBlocks = $selectedBlocks;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedBlocks()
    {
        if ($selectedBlocks = $this->getRequest()->getParam('selected_blocks', null)) {
            $this->setSelectedBlocks($selectedBlocks);
        }
        return $this->_selectedBlocks;
    }
}
