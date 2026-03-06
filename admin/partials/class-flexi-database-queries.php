<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/admin
 */
if (!defined('ABSPATH')) {
    die;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Flexi_Abandon_Cart_Recovery
 * @subpackage Flexi_Abandon_Cart_Recovery/admin
 * @author     Start and Grow <test@gmailcom>
 */
class Flexi_Database_Queries
{

    /**
     * Instance of the activator class.
     *
     * @var Flexi_Abandon_Cart_Recovery_Activator
     */
    private $acr_activator_instance;

    /**
     * Constructor.
     */
    public function __construct()
    {
        include_once FLEXI_ABANDON_CART_RECOVERY_DIR . 'includes/class-flexi-abandon-cart-recovery-activator.php';
        $this->acr_activator_instance = new Flexi_Abandon_Cart_Recovery_Activator();
    }

    /**
     * Executes a SELECT query.
     *
     * @param string       $table     Table name.
     * @param string|array $paramter Columns to select.
     * @param string|array $where     Optional. WHERE clause.
     * @param string|array $and_param Optional. Additional AND clause.
     * @param int|null     $limit     Optional. LIMIT clause.
     * @param int|null     $offset    Optional. OFFSET clause.
     * @return array       Query results.
     */
    public function select_db_query($table, $paramter, $where = '', $and_param = '', $limit = null, $offset = null)
    {
        global $wpdb;
        $where_clauses = array();
        $where_values  = array();

        if (is_array($paramter)) {
            $paramter = implode(', ', $paramter);
        }
        $table_name = $wpdb->prefix . $table;
        $query      = "SELECT $paramter FROM $table_name";

        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $column => $value) {
                    $where_clauses[  ] = "$column = %s";
                    $where_values[  ]  = $value;
                }
            } else {
                $where_clauses[  ] = $where;
            }
        }

        if (!empty($and_param)) {
            if (is_array($and_param)) {
                foreach ($and_param as $column => $value) {
                    $where_clauses[  ] = "$column = %s";
                    $where_values[  ]  = $value;
                }
            } else {
                $where_clauses[  ] = $and_param;
            }
        }

        if (!empty($where_clauses)) {
            $query .= ' WHERE ' . implode(' AND ', $where_clauses);
        }
        if ($limit) {
            $query .= $wpdb->prepare(' LIMIT %d', $limit);
            if ($offset) {
                $query .= $wpdb->prepare(' OFFSET %d', $offset);
            }
        }

        $query .= ';';
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, ...$where_values), ARRAY_A);
        } else {
            return $wpdb->get_results($query, ARRAY_A);
        }
    }
    /**
     * Executes an UPDATE query.
     *
     * @param string $table     Table name.
     * @param array  $parameters Key-value pairs to update.
     * @param string $where     Optional. WHERE clause.
     * @param string $and_param Optional. Additional AND clause.
     * @return int         Number of rows affected.
     */
    public function update_db_query($table, $parameters, $where = '', $and_param = '')
    {
        global $wpdb;

        $set_clause = array();
        foreach ($parameters as $column => $value) {

            if (preg_match("/$column/", $value)) {
                $set_clause[  ] = "{$column} = {$value}";
            } else {
                $set_clause[  ] = "{$column} = " . $wpdb->prepare('%s', $value);
            }
        }
        $set_clause = implode(', ', $set_clause);

        $table_name = $wpdb->prefix . $table;

        $query = "UPDATE $table_name SET $set_clause";

        if (null !== $where && '' !== $where) {
            $query .= " WHERE $where";
        }
        if (null !== $and_param && '' !== $and_param) {
            $query .= " AND $and_param";
        }

        $query .= ';';
        $result = $wpdb->query($query);

        // print_r($result);
        // die;
        return $result;
    }

    /**
     * Executes an INSERT query.
     *
     * @param string $table     Table name.
     * @param array  $parameters Key-value pairs to insert.
     * @return int   Number of rows affected.
     */
    public function insert_db_query($table, $parameters)
    {
        global $wpdb;

        $table_name   = $wpdb->prefix . $table;
        $columns      = array();
        $placeholders = array();
        $values       = array();

        foreach ($parameters as $column => $value) {
            $columns[  ]      = $column;
            $placeholders[  ] = '%s';
            $values[  ]       = $value;
        }

        $columns      = implode(', ', $columns);
        $placeholders = implode(', ', $placeholders);
        $query        = "INSERT INTO $table_name ($columns) VALUES ($placeholders);";
        
        $wpdb->query($wpdb->prepare($query, ...$values));
        return $wpdb->insert_id;
    }

    /**
     * Executes a DELETE query.
     *
     * @param string $table     Table name.
     * @param string $where     WHERE clause.
     * @param string $and_param Optional. Additional AND clause.
     * @return int   Number of rows affected.
     */

    /**
     * Executes a DELETE query.
     *
     * @param string $table     Table name.
     * @param array  $where     WHERE clause as key-value pairs.
     * @param array  $and_param Optional. Additional AND clause as key-value pairs.
     * @return int   Number of rows affected.
     */
    public function delete_db_query( $table, $where, $and_param = array() ) {
		global $wpdb;

		$where_clauses = array();
		$where_values  = array();

		foreach ( $where as $column => $value ) {
			$where_clauses[] = "$column = %s";
			$where_values[]  = $value;
		}

		if ( ! empty( $and_param ) ) {
			foreach ( $and_param as $column => $value ) {
				$where_clauses[] = "$column = %s";
				$where_values[]  = $value;
			}
		}

		$table_name = $wpdb->prefix . $table;
		$query      = "DELETE FROM $table_name WHERE " . implode( ' AND ', $where_clauses ) . ';';

		return $wpdb->query( $wpdb->prepare( $query, ...$where_values ) );
	}


    /**
     * Retrieves data from a specified table with sorting and pagination.
     *
     * @param string $table_name The name of the table.
     * @param string $select_columns The columns to select, or '*' for all columns.
     * @param array  $conditions An associative array of conditions for the WHERE clause.
     * @param string $orderby The column to sort by.
     * @param string $order The sorting direction (ASC or DESC).
     * @param int    $per_page The number of items per page.
     * @param int    $offset The offset for pagination.
     * @return array The results of the query.
     */
    public function get_sorted_result($table_name, $select_columns = '*', $conditions = array(), $orderby = 'id', $order = 'ASC', $per_page = 20, $offset = 0)
    {
        global $wpdb;

        $table_name     = $table_name;
        $select_columns = $select_columns;
        $orderby        = $orderby;
        $order          = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $per_page       = absint($per_page);
        $offset         = absint($offset);

        $where_clauses = array();
        foreach ($conditions as $key => $value) {
            $where_clauses[  ] = "$key = '$value'";
        }
        $where_clause = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        $query = sprintf(
            'SELECT %s FROM %s %s ORDER BY %s %s LIMIT %d OFFSET %d',
            $select_columns,
            $wpdb->prefix . $table_name,
            $where_clause,
            $orderby,
            $order,
            $per_page,
            $offset
        );

        return $wpdb->get_results($query, ARRAY_A);
    }
}
