<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class DealActions extends Column
{

    /** Url path */
    const GRID_URL_PATH_EDIT = 'dailydeal/deal/edit';
    const GRID_URL_PATH_REPORT = 'dailydeal/deal/report';

    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['deal_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::GRID_URL_PATH_EDIT, ['deal_id' => $item['deal_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['report'] = [
                        'href' => $this->urlBuilder->getUrl(self::GRID_URL_PATH_REPORT,
                            ['deal_id' => $item['deal_id']]),
                        'label' => __('Report')
                    ];
                }
            }
        }
        return $dataSource;
    }

}
