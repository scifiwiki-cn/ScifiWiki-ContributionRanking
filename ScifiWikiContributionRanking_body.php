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

    function execute ( $par ) {

    }

    function getGroupName () {
	    return 'users';
    }

}
