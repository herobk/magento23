<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tigren\Bannermanager\Controller\Adminhtml\Block;

class Blocks extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{

    public function execute()
    {

        $selected = $this->getRequest()->getParam('selected', '');

        $chooser = $this->_view->getLayout()->createBlock(
            'Tigren\Bannermanager\Block\Adminhtml\Block\Widget\Chooser'
        )->setName(
            $this->mathRandom->getUniqueHash('blocks_grid_')
        )->setUseMassaction(
            true
        )
            ->setSelectedBlocks(
                explode(',', $selected)
            );

        $serializer = $this->_view->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Grid\Serializer',
            '',
            [
                'data' => [
                    'grid_block' => $chooser,
                    'callback' => 'getSelectedBlocks',
                    'input_element_name' => 'selected_blocks',
                    'reload_param_name' => 'selected_blocks',
                ]
            ]
        );
        $this->setBody($chooser->toHtml() . $serializer->toHtml());
    }
}
