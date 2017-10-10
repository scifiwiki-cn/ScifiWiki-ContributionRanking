<?php
/** \file
 * \brief Contains code for the ContributionScores Class (extends SpecialPage).
 */

/// Special page class for the Contribution Scores extension
/**
 * Special page that generates a list of wiki contributors based
 * on edit diversity (unique pages edited) and edit volume (total
 * number of edits.
 *
 * @ingroup Extensions
 * @author Tim Laqua <t.laqua@gmail.com>
 */
class ScifiWikiContributionRanking extends IncludableSpecialPage {
	public function __construct() {
		parent::__construct( 'ScifiWikiContributionRanking' );
	}

	function getMiniRanking() {
        $dbr = wfGetDB( DB_REPLICA );

        $userTable = $dbr->tableName( 'user' );
        $userGroupTable = $dbr->tableName( 'user_groups' );
        $revTable = $dbr->tableName( 'revision' );
        $ipBlocksTable = $dbr->tableName( 'ipblocks' );
        $sqlWhere = "";
        $nextPrefix = "WHERE";

        $date = time() - ( 60 * 60 * 24 * 7 );
        $dateString = $dbr->timestamp( $date );
        $sqlWhere .= " {$nextPrefix} rev_timestamp > '$dateString'";
        $nextPrefix = "AND";

        $sqlWhere .= " {$nextPrefix} rev_user NOT IN " .
            "(SELECT ipb_user FROM {$ipBlocksTable} WHERE ipb_user <> 0)";
        $nextPrefix = "AND";

        $sqlWhere .= " {$nextPrefix} rev_user NOT IN " .
            "(SELECT ug_user FROM {$userGroupTable} WHERE ug_group='bot')";

        $sqlMostPages = "SELECT rev_user,
						 COUNT(DISTINCT rev_page) AS page_count,
						 COUNT(rev_id) AS rev_count
						 FROM {$revTable}
						 {$sqlWhere}
						 GROUP BY rev_user
						 ORDER BY page_count DESC
						 LIMIT 10";

        $sql = "SELECT user_id, " .
            "user_name, " .
            "user_real_name, " .
            "page_count " .
            "FROM $userTable u JOIN ($sqlMostPages) s ON (user_id=rev_user) " .
            "ORDER BY page_count DESC " .
            "LIMIT 10";

        $res = $dbr->query($sql);

        $output = "<table class=\"swcr-table\" style='border: none;' >\n";

        $user_rank = 1;

        foreach($res as $row) {
            $userLink = Linker::userLink(
                $row->user_id,
                $row->user_name
            );
            $output .= "<tr style='background: none;'>";
            if ($user_rank <= 3) {
                $output .= "<td style='text-align: center;'><span style='font-size: 16px; padding: 2px; color: #008CBA; border: 1px solid #008CBA; border-radius: 10px; line-height: 16px; width: 21px; height: 21px; display: inline-block;'>" . $user_rank . "</span></td>";
            }
            else {
                $output .= "<td style='text-align: center;'><span style='font-size: 16px; padding: 2px; color: #008CBA; line-height: 16px; width: 21px; height: 21px; display: inline-block;'>" . $user_rank . "</span></td>";
            }
            $output .= "<td style='font-size: 14px;'>" . $userLink . "共编辑词条<b style='color: #BA0000; margin-left: 5px; margin-right: 5px;'>". $row->page_count ."</b>条</td>";
            $output .= Html::closeElement('tr');

            $user_rank++;
        }

        $output .= Html::closeElement( 'table' );
        $dbr->freeResult( $res );

        return $output;
    }

    function getFullRanking() {
        $dbr = wfGetDB( DB_REPLICA );

        $userTable = $dbr->tableName( 'user' );
        $userGroupTable = $dbr->tableName( 'user_groups' );
        $revTable = $dbr->tableName( 'revision' );
        $ipBlocksTable = $dbr->tableName( 'ipblocks' );
        $sqlWhere = "";
        $nextPrefix = "WHERE";

        $date = time() - ( 60 * 60 * 24 * 7 );
        $dateString = $dbr->timestamp( $date );
        $sqlWhere .= " {$nextPrefix} rev_timestamp > '$dateString'";
        $nextPrefix = "AND";

        $sqlWhere .= " {$nextPrefix} rev_user NOT IN " .
            "(SELECT ipb_user FROM {$ipBlocksTable} WHERE ipb_user <> 0)";
        $nextPrefix = "AND";

        $sqlWhere .= " {$nextPrefix} rev_user NOT IN " .
            "(SELECT ug_user FROM {$userGroupTable} WHERE ug_group='bot')";

        $sqlMostPages = "SELECT rev_user,
                     COUNT(DISTINCT rev_page) AS page_count,
                     COUNT(rev_id) AS rev_count
                     FROM {$revTable}
                     {$sqlWhere}
                     GROUP BY rev_user
                     ORDER BY page_count DESC
                     LIMIT 100";

        $sql = "SELECT user_id, " .
            "user_name, " .
            "user_real_name, " .
            "page_count, " .
            "rev_count " .
            "FROM $userTable u JOIN ($sqlMostPages) s ON (user_id=rev_user) " .
            "ORDER BY page_count DESC " .
            "LIMIT 100";

        $res = $dbr->query($sql);

        $output = "<table class=\"swcr-table\" >\n" .
            "<tr class='header'>\n" .
            Html::element( 'th', ["style" => "text-align: center;"], $this->msg( 'scifiwikicontributionranking-rank' )->text() ) .
            Html::element( 'th', ["style" => "text-align: center;"], $this->msg( 'scifiwikicontributionranking-pages' )->text() ) .
            Html::element( 'th', ["style" => "text-align: center;"], $this->msg( 'scifiwikicontributionranking-changes' )->text() ) .
            Html::element( 'th', ["style" => "text-align: center;"], $this->msg( 'scifiwikicontributionranking-username' )->text() ) .
            Html::closeElement('tr');

        $user_rank = 1;

        foreach($res as $row) {
            $userLink = Linker::userLink(
                $row->user_id,
                $row->user_name
            );
            $output .= "<tr class='content'>";
            $output .= "<td class='rank' style='text-align: center;'><span>" . $user_rank . "</span></td>";
            $output .= "<td style='text-align: center;'>" . $row->page_count . "</td>";
            $output .= "<td style='text-align: center;'>" . $row->rev_count . "</td>";
            $output .= "<td style='text-align: center;'>" . $userLink;

            $output .= Linker::userToolLinks( $row->user_id, $row->user_name );
            $output .= Html::closeElement('td');
            $output .= Html::closeElement('tr');

            $user_rank++;
        }

        $output .= Html::closeElement( 'table' );
        $dbr->freeResult( $res );

        return $output;
    }

	function showPage($mode) {
	    if ($mode == 'mini') {
            $this->getOutput()->addHTML( $this->getMiniRanking());
        }
        else {
            $this->getOutput()->addHTML( $this->getFullRanking());
        }
    }

    function execute ( $par ) {
        $this->setHeaders();

        $this->showPage($this->getMode($par));

    }

    function getMode ($par) {
	    if (!empty($par)) {
	        return $par == "mini" ? "mini" : "full";
        }
        else {
	        return "full";
        }
    }

    function getGroupName () {
	    return 'users';
    }

}
