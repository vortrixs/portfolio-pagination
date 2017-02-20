<?php

/**
 * Description of Pager
 *
 * @author Hansi
 */

class Pagination {

    private $db;
    private $start;
    private $pagination;
    private $previous;
    private $next;
    private $first_link = true;
    private $total_pages;

    public $current_page = 1;
    public $per_page = 25;

    const LINK_OFFSET = 3;
    const PAGING_OFFSET = 2;

    public function __construct(DB $db) {
        $this->db = $db;
    }

    public function setter($page = null, $per_page = null, $max_per_page = null) {
        if (!empty($page)) {
            $this->current_page = (int) $page;
        }
        if (!empty($per_page)) {
            $this->per_page = $per_page <= 100 ? (int) $per_page : $this->per_page;
        }
    }

    /**
     * Creates the data shown on the page, run the generateLinks function after formatting data from this.
     *
     * Uses the DB::action function, refer to the DB class documentation for further info.
     *
     * @param array $data
     * @param string $table
     * @return array containing objects
     */
    public function pagination($data = array(), $table) {

        $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $data) . ", FOUND_ROWS() AS total";

        $this->positioning();

        $this->db->action($sql, $table, null, array('LIMIT' => "{$this->start}, {$this->per_page}"));

        return $this->db->results();
    }

    /**
     * Calculates where to start the query made in the pagination function.
     */
    private function positioning() {
        $this->start = ($this->current_page > 1) ? ($this->current_page * $this->per_page) - $this->per_page : 0;
    }

    /**
     * Generates the amount of pages needed from the value entered in the constructer.
     *
     * @return string
     */
    public function generateLinks() {

        $query = $this->db->query("SELECT FOUND_ROWS() AS total");

        $results = $query->results();
        $total = (int) $results[0]->total;
        $this->total_pages = ceil($total / $this->per_page);

        if ($this->total_pages > 0 && $this->total_pages != 1 && $this->current_page <= $this->total_pages) {

            $this->pagination .= '<ul class="pagination">';
            $this->next = $this->current_page + Pagination::LINK_OFFSET;
            $this->previous = $this->current_page - Pagination::LINK_OFFSET;

            if ($this->current_page > 1) {
                $this->create_previous_links();
            }

            $this->create_active_link();

            if ($this->current_page < $this->total_pages) {
                $this->create_next_links();
            }

            $this->pagination .= '</ul>';
        }
        return $this->pagination;
    }

    private function create_previous_links() {
        $previous_link = ($this->previous == 0) ? 1 : $this->previous;
        $this->pagination .= '<li class="page first"><a href="#" data-page="1" title="First">&laquo;</a></li>';
        $this->pagination .= '<li class="page"><a href="#" data-page="' . ($previous_link + Pagination::PAGING_OFFSET) . '" title="Previous">&lt;</a></li>';
        for ($previous_page_number = ($this->current_page - Pagination::PAGING_OFFSET); $previous_page_number < $this->current_page; $previous_page_number++) {
            if ($previous_page_number > 0) {
                $this->pagination .= '<li class="page"><a href="#" data-page="' . $previous_page_number . '" title="Page' . $previous_page_number . '">' . $previous_page_number . '</a></li>';
            }
        }
        $this->first_link = false;
    }

    private function create_active_link() {
        if ($this->first_link) {
            $this->pagination .= '<li class="page first active">' . $this->current_page . '</li>';
        } elseif ($this->current_page == $this->total_pages) {
            $this->pagination .= '<li class="page last active">' . $this->current_page . '</li>';
        } else {
            $this->pagination .= '<li class="page active">' . $this->current_page . '</li>';
        }
    }

    private function create_next_links() {
        for ($next_page_number = $this->current_page + 1; $next_page_number < $this->next ; $next_page_number++) {
            if ($next_page_number <= $this->total_pages){
                $this->pagination .= '<li class="page"><a href="#" data-page="' . $next_page_number . '" title="Page ' . $next_page_number . '">' . $next_page_number . '</a></li>';
            }
        }

        if ($this->current_page < $this->total_pages) {
            $next_link = ($next_page_number > $this->total_pages) ? $this->total_pages : $next_page_number;
            $this->pagination .= '<li class="page"><a href="#" data-page="' . ($next_link) . '" title="Next">&gt;</a></li>';
            $this->pagination .= '<li class="page last"><a href="#" data-page="' . $this->total_pages . '" title="Last">&raquo;</a></li>';
        }
    }

}