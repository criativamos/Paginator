<?php

namespace Criativamos\Paginator;

/**
 * Class Paginator
 */
class Paginator{

    /**
     * @var \PDO
     */
    private $db = null;
    /**
     * @var string
     */
    private $pageVar = 'page';
    /**
     * @var int
     */
    private $resultsPerPage = 15;

    /**
     * @var null
     */
    private $query = null;

    /**
     * @var null
     */
    private $originalQuery = null;
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var bool
     */
    private $executed = false;

    /**
     * @var int
     */
//    private $navLimit = 5;
//
    /**
     * @var array
     */
    private $results = [
        'currentPage'   => 1,
        'total'         => 0,
        'perPage'       => 0,
        'lastPage'      => 0,
        'nextPageUrl'   => '',
        'prevPageUrl'   => '',
        'currentUrl'    => '',
        'from'          => 1,
        'to'            => 1,
        'data'          => null
    ];

    /**
     * Paginator constructor.
     * @param \PDO $databaseConnection
     * @param null $query
     * @param int $resultsPerPage
     */
    public function __construct(\PDO $databaseConnection, $query = null, $resultsPerPage = 15)
    {
        if(isset($databaseConnection))
            $this->db = $databaseConnection;

        if($query)
            $this->setQuery($query);

        $this->resultsPerPage = $resultsPerPage;
    }

    public function setNavigationLimit($navLimit = 5)
    {
        $this->navLimit = $navLimit;
    }

    /**
     * @param $numPerPage
     * @return $this
     */
    public function setResultsPerPage($numPerPage)
    {
        $this->resultsPerPage = $numPerPage;
        return $this;
    }

    /**
     * @param $sqlQuery
     * @param array $parameters
     * @return $this
     */
    public function setQuery($sqlQuery, $parameters = [])
    {
        $this->query = $sqlQuery;
        if(is_array($parameters) && count($parameters) > 0)
            $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters = [])
    {
        if(is_array($parameters) && count($parameters) > 0)
            $this->parameters = $parameters;
    }

    /**
     * @return int
     */
    private function currentPage()
    {
        return (int) isset($_GET[$this->pageVar]) ? $_GET[$this->pageVar] : 1;
    }

    /**
     * @return mixed
     */
    private function total()
    {
        $pattern = '/^(?:SELECT)(.+)(?:FROM)/is';
        preg_match($pattern, $this->originalQuery, $matches);
        $query = str_replace($matches[1], ' COUNT(0) AS TOTAL ', $this->originalQuery);
        $stmt = $this->db->prepare($query);
        $stmt->execute($this->parameters);
        $this->results['total'] = (int) $stmt->fetch(\PDO::FETCH_OBJ)->TOTAL;

        return $this->results['total'];
    }

    /**
     * @return string
     */

    private function limit()
    {
        $limit = '0,' . $this->resultsPerPage;
        $currentPage = $this->currentPage();
        $this->results['from'] = 1;
        $this->results['to'] = $this->resultsPerPage;

        if($currentPage > 1){
            $offset = ($currentPage - 1)  * $this->resultsPerPage;
            $last = $offset + $this->resultsPerPage;

            $this->results['from'] = $offset + 1;
            $this->results['to'] = $last;

            $limit = $offset . ', ' . $this->resultsPerPage;
        }

        return ' LIMIT ' . $limit;
    }

    /**
     * @return null|string
     */
    private function prepareQuery()
    {
        $limit = $this->limit();

        $this->originalQuery = $this->query;
        $this->query .= $limit;

        return $this->query;
    }

    /**
     * @return string
     */
    private function uri()
    {
        $urlProtocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
        $removeParameter = strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');
        $uri = $urlProtocol.$_SERVER['HTTP_HOST'].'/'.$removeParameter;
        return $uri;
    }

    private function nextUrl()
    {
        $uri = $this->uri();
        if(isset($_GET[$this->pageVar])) {
            $urlParameters = $_GET;
            $urlParameters[$this->pageVar] = (int) $urlParameters[$this->pageVar] + 1;
            $buildUrlQuery = http_build_query($urlParameters);
            $uri .= '?'.$buildUrlQuery;
        } else {
            if(strpos($_SERVER['REQUEST_URI'], '?') !== false)
                $uri .= '&page='.($this->currentPage() + 1);
            else
                $uri .= '?page=2';
        }

        return $uri;
    }

    private function prevUrl()
    {
        $uri = $this->uri();
        if($this->currentPage() > 0) {
            $urlParameters = $_GET;
            if(isset($urlParameters[$this->pageVar])) {
                $urlParameters[$this->pageVar] = (int)$urlParameters[$this->pageVar] - 1;
                $buildUrlQuery = http_build_query($urlParameters);
                $uri .= '?' . $buildUrlQuery;
            }
        }

        return $uri;
    }

    private function currentUrl()
    {
        $uri = $this->uri();
        if($this->currentPage() > 0) {
            $urlParameters = $_GET;
            if(isset($urlParameters[$this->pageVar])) {
                $urlParameters[$this->pageVar] = (int)$urlParameters[$this->pageVar];
                $buildUrlQuery = http_build_query($urlParameters);
                $uri .= '?' . $buildUrlQuery;
            }
        }

        return $uri;
    }

    private function buildPageUrl($pageNumber)
    {
        $uri = $this->uri();
        if($this->currentPage() > 0) {
            $urlParameters = $_GET;
            $urlParameters[$this->pageVar] = (int) $pageNumber;
            $buildUrlQuery = http_build_query($urlParameters);
            $uri .= '?'.$buildUrlQuery;
        }

        return $uri;
    }

    public function results()
    {
        if(!$this->executed) {
            //Prepare Statement
            $this->prepareQuery();
            //Current Page
            $this->results['currentPage'] = $this->currentPage();
            //Total query results
            $this->results['total'] = $this->total();
            //Results per page
            $this->results['perPage'] = $this->resultsPerPage;
            //Last Page
            $lastPage = ceil($this->results['total'] / $this->resultsPerPage);
            $this->results['lastPage'] = $lastPage < 1 ? 1 : $lastPage;
            //Next URL
            $this->results['nextPageUrl'] = $this->nextUrl();
            //Prev Url
            $this->results['prevPageUrl'] = $this->prevUrl();
            //Current Url
            $this->results['currentUrl'] = $this->currentUrl();
            //Execute query
            $stmt = $this->db->prepare($this->query);
            $stmt->execute($this->parameters);
            $this->executed = true;
            //Data
            $data = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $this->results['data'] = $data;
        }

        return $this->results;
    }

    public function getData()
    {
        return $this->results()['data'];
    }

    public function rowCount()
    {
        return count($this->results()['data']);
    }

    public function flush()
    {
        $this->query = null;
        $this->parameters = [];
        $this->results = [
            'currentPage'   => 1,
            'total'         => 0,
            'perPage'       => 0,
            'lastPage'      => 0,
            'nextPageUrl'   => '',
            'currentUrl'    => '',
            'prevPageUrl'   => '',
            'from'          => 1,
            'to'            => 1,
            'data'          => null
        ];

    }

    public function reset()
    {
        $this->flush();
        $this->pageVar = 'page';
        $this->resultsPerPage = 15;
    }

    public function render()
    {
        $result = $this->results();
        if($result['total'] > 0){
            $page = $this->currentPage();
            $adjacents = 2;
            $next = $page + 1;
            $lastpage = $result['lastPage'];
            $lpm1 = $lastpage - 1;
            $pagination = "";
            if($lastpage > 1)
            {
                $counter = 1;
                $pagination .= "<ul class='pagination'>";
                if ($page > 1)
                    $pagination.= '<li><a href="'.$result['prevPageUrl'].'">&laquo;</a></li>';
                else
                    $pagination.= "<li class='disabled'><a>&laquo;</a></li>";
                if ($lastpage < 7 + ($adjacents * 2))
                {
                    for ($counter = 1; $counter <= $lastpage; $counter++)
                    {
                        if ($counter == $page)
                            $pagination.= '<li class="active"><a href="'.$this->currentUrl().'">'.$counter.'</a></li>';
                        else
                            $pagination.= '<li><a href="'.$this->buildPageUrl($counter).'">'.$counter.'</a></li>';
                    }
                }

                elseif($lastpage > 5 + ($adjacents * 2))
                {
                    if($page < 1 + ($adjacents * 2))
                    {
                        for($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                        {
                            if($counter == $page)
                                $pagination.= "<li class='active'><a>$counter</a></li>";
                            else
                                $pagination.= '<li><a href="'.$this->buildPageUrl($counter).'">'.$counter.'</a>';
                        }
                        $pagination.= "<li><a>...</a></li>";
                        $pagination.= '<li><a href="'.$this->buildPageUrl($lpm1).'">'.$lpm1.'</a></li>';
                        $pagination.= '<li><a href="'.$this->buildPageUrl($lastpage).'">'.$lastpage.'</a></li>';

                    }
                    elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
                    {
                        $pagination.= '<li><a href="'.$this->buildPageUrl(1).'">1</a></li>';
                        $pagination.= '<li><a href="'.$this->buildPageUrl(2).'">2</a></li>';
                        $pagination.= "<li><a>...</a></li>";
                        for($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                        {
                            if($counter == $page)
                                $pagination.= "<li class='active'><a>$counter</a></li>";
                            else
                                $pagination.= '<li><a href="'.$this->buildPageUrl($counter).'">'.$counter.'</a></li>';
                        }
                        $pagination.= "<li><a>...</a></li>";
                        $pagination.= '<li><a href="'.$this->buildPageUrl($lpm1).'">'.$lpm1.'</a></li>';
                        $pagination.= '<li><a href="'.$this->buildPageUrl($lastpage).'">'.$lastpage.'</a></li>';
                    }
                    else
                    {
                        $pagination.= '<li><a href="'.$this->buildPageUrl(1).'">1</a></li>';
                        $pagination.= '<li><a href="'.$this->buildPageUrl(2).'">2</a></li>';
                        $pagination.= "<li><a>...</a></li>";
                        for($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
                        {
                            if($counter == $page)
                                $pagination.= "<li class='active'><a>$counter</a></li>";
                            else
                                $pagination.= '<li><a href="'.$this->buildPageUrl($counter).'">'.$counter.'</a></li>';
                        }
                    }
                }
                if($page < $counter - 1)
                    $pagination.= '<li><a href="'.$this->buildPageUrl($next).'">&raquo;</a></li>';
                else
                    $pagination.= "<li class='disabled'><a>&raquo;</a></li>";

                $pagination.= "</ul>";
            }

            echo $pagination;
        }
    }

}