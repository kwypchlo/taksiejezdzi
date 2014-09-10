<?php
/**
 * Created by PhpStorm.
 * User: Mar
 * Date: 28.08.14
 * Time: 17:11
 */

namespace tsj\MemsBundle\Pagination;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ReversePagination
{
    private $count;
    private $currentPage;
    private $totalPages;
    private $perPage;
    private $navigationAdditionalPagesCount;
    private $navigationParams = [];
    private $showFullPages;
    private $queryObj;

    /**
     * @param EntityManager $em
     * @param $perPage
     * @param $showFullPages
     * @param int $navigationAdditionalPagesCount
     */
    public function __construct(EntityManager $em, $perPage, $showFullPages, $navigationAdditionalPagesCount = 2)
    {
        $this->queryObj = $em->createQueryBuilder();
        $this->perPage = $perPage;
        $this->showFullPages = $showFullPages;
        //ilosc stron do pokazania w paginacji
        $this->navigationAdditionalPagesCount = $navigationAdditionalPagesCount;
    }

    /**
     * Funkcja zwracająca tablice itemow dla wybranej strony, na podstawie przekazanego zapytania w query builderze
     * @param QueryBuilder $query
     * @param int $page
     * @param null $perPage
     * @param null $showFullPages
     * @return array
     */
    public function paginate(QueryBuilder $query, $page = 0, $perPage = null, $showFullPages = null )
    {
        //parametry zapytania do paginacji
        $this->queryObj = $query;
        //aktualna strona - jesli nie zostala przekazana, wyswietlamy "maksymalna" strone
        if ((int)$page > 0)
            $this->currentPage = $page;
        if (!empty($perPage))
            $this->perPage = $perPage;
        if (!is_null($showFullPages))
            $this->showFullPages = $showFullPages;

        //ustawienie maksymalnej ilosci stron
        $this->setTotalPages();
        //parametry do zbudowania nawigacji
        $this->setNavigationParams();

        return $this->getPageItems();
    }

    /**
     * Metoda oblicza ilosc wszystkich itemow i stron, przed pobraniem paginacji, ktore sa potrzebne do obliczenia offsetu
     */
    private function setTotalPages()
    {
        $qb = clone($this->queryObj);
        $rootAliases = $qb->getRootAliases();
        $qb->select("COUNT(".$rootAliases[0].".id)");
        $this->count = $qb->getQuery()->getSingleScalarResult();

        $totalPagesTemp = $this->count / $this->perPage;
        $totalPages = $this->showFullPages ? floor($totalPagesTemp) : ceil($totalPagesTemp);
        //jesli pokazujemy tylko pelne strony i jest mniej obrazkow niz na jedna strone, trzeba je wyswietlic (1 strona)
        if ($this->showFullPages && $totalPages === 0)
            $totalPages = 1;

        $this->totalPages = $totalPages;
        $this->validateCurrentPage();
    }

    /**
     * Metoda sprawdza czy numer strony z requesta jest prawidlowy - jesli nie, ustawia max. strone do wyswietlenia
     */
    private function validateCurrentPage()
    {
        if (empty($this->totalPages) || ((int)($this->currentPage) && $this->currentPage <= $this->totalPages))
            return;
        $this->currentPage = $this->totalPages;
    }

    /**
     * Pobiera i zwraca elementy dla aktualnie wybranej strony
     * Offset liczony inaczej niz w przypadku zwyklej pagiancji, dla pierwszej strony moze wyjsc mniejszy niz 0
     * @return array
     */
    private function getPageItems()
    {
        $offset = $this->count - ($this->perPage * $this->currentPage);
        if ($offset < 0)
            $offset = 0;
        $this->queryObj
            ->setFirstResult($offset)
            ->setMaxResults($this->perPage);
        return $this->queryObj->getQuery()->getResult();
    }

    /**
     * Ustawia tablice parametrow potrzebnych do zbudowania paginacji w widoku
     *      'page_min' => poczatkowa strona,
     *      'page_max' => koncowa strona,
     *      'has_next' => czy jestesmy na pierwszej stronie, czy sa kolejne
     *      'has_prev' => czy jestesmy na ostatniej stronie
     */
    private function setNavigationParams()
    {
        $showPages = $this->getNavigationPages();
        $hasNext = $this->currentPage > 1 ? true : false;
        $hasPrevious = $this->totalPages - $this->currentPage > 0 ? true : false;
        $this->navigationParams = [
            'page_min' => $showPages['page_min'],
            'page_max' => $showPages['page_max'],
            'has_next' => $hasNext,
            'has_prev' => $hasPrevious
        ];
    }

    /**
     * Zwraca tablice z wartosciami strony minimalnej i maksymalnej jakie nalezy pokazac w linkach nawigacji
     *
     * @return array
     */
    private function getNavigationPages()
    {
        $pagesToShow = $this->navigationAdditionalPagesCount * 2 + 1;
        if ($this->totalPages <= $pagesToShow){
            $pageMin = 1;
            $pageMax = $this->totalPages;
        }else{
            $lowerPagesAvailable = $this->currentPage - 1;
            $higherPagesAvailable = $this->totalPages - $this->currentPage;
            $lowerPagesDeficit = $lowerPagesAvailable - $this->navigationAdditionalPagesCount;
            $higherPagesDeficit = $higherPagesAvailable - $this->navigationAdditionalPagesCount;
            $extraHigherPages = $lowerPagesDeficit < 0 ? abs($lowerPagesDeficit) : 0;
            $extraLowerPages = $higherPagesDeficit < 0 ? abs($higherPagesDeficit) : 0;

            $pageMin = $this->currentPage - ($this->navigationAdditionalPagesCount + $extraLowerPages - $extraHigherPages);
            $pageMax = $this->currentPage + ($this->navigationAdditionalPagesCount + $extraHigherPages - $extraLowerPages);
        }
        return ['page_min' => $pageMin, 'page_max' => $pageMax];
    }

    /**
     * @return array
     */
    public function getNavigationParams()
    {
        return $this->navigationParams;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

}