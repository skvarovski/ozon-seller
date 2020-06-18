<?php declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Enum\TransactionType;
use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Allows to retrieve reports via API, that are also available in Seller Center.
 *
 * @see https://cb-api.ozonru.me/apiref/en/#t-title_seller_reports
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class ReportService extends AbstractService
{
    /**
     * Returns a list of reports which were previously generated by Seller.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_post_reportlist
     *
     * @return array|string
     */
    public function list(array $query)
    {
        $query = $this->faceControl($query, ['page', 'page_size', 'report_type']);

        return $this->request('POST', '/v1/report/list', $query);
    }

    /**
     * Get report info by unique ID.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_post_reportinfo
     *
     * @return array|string
     */
    public function info(string $code = null)
    {
        $query = array_filter(['code' => $code]);

        return $this->request('POST', '/v1/report/info', $query);
    }

    /**
     * Returns products reports, which is also available in Seller Center.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_post_reportproducts
     *
     * @param array $query ['offer_id', 'search', 'sku', 'visibility']
     *
     * @return array
     */
    public function products(array $query = [])
    {
        $query = $this->faceControl($query, ['offer_id', 'search', 'sku', 'visibility']);
        $query = array_filter($query);

        return $this->request('POST', '/v1/report/products/create', $query);
    }

    /**
     * Returns products reports, which is also available in Seller Center.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_post_reporttransactions
     *
     * @return array|string
     */
    public function transaction(\DateTimeInterface $dateFrom, \DateTimeInterface $dateTo, string $search = null, string $transactionType = TransactionType::ALL)
    {
        $query = array_filter([
            'date_from'        => $dateFrom->format('Y-m-d'),
            'date_to'          => $dateTo->format('Y-m-d'),
            'search'           => $search,
            'transaction_type' => $transactionType,
        ]);

        return $this->request('POST', '/v1/report/transactions/create', $query);
    }
}
