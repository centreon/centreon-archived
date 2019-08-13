<?php

namespace Centreon\Domain\Pagination;

class SortRequest
{
    const NAME_FOR_SORT = 'sort_by';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const DEFAULT_ORDER = self::ORDER_ASC;

    private $authorizedOrders = [self::ORDER_ASC, self::ORDER_DESC];

    /**
     * @var array Field to order
     */
    private $sort;

    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param string $sortRequest
     */
    public function setSort(string $sortRequest)
    {
        $sortToAnalize = json_decode($sortRequest ?? '{}', true);
        if (!is_array($sortToAnalize)) {
            $this->sort = [$sortRequest => self::ORDER_ASC];
        } else {
            foreach ($sortToAnalize as $name => $order) {
                $isMatched = preg_match(
                    '/^([a-zA-Z0-9_.-]*)$/i',
                    $name,
                    $sortFound,
                    PREG_OFFSET_CAPTURE
                );
                if (!$isMatched || !in_array(strtoupper($order), $this->authorizedOrders)) {
                    unset($sortToAnalize[$name]);
                } else {
                    $sortToAnalize[$name] = strtoupper($order);
                }
            }
            $this->sort = $sortToAnalize;
        }
    }
}
