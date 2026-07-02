<?php
/**
 * DMF override — framework listing card → Visit Marrakech design cards.
 *
 * Any place DMF Core renders "cards/listing" (archives, blocks,
 * shortcodes) now outputs the design-board-1A card for the listing's kind.
 *
 * @var array $args { post_id: int }
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

$vm_id   = (int) ( $args['post_id'] ?? get_the_ID() );
$vm_kind = mcb_listing_kind( $vm_id );
$vm_card = in_array( $vm_kind, array( 'venue', 'hotel', 'experience', 'event' ), true ) ? $vm_kind : 'listing';

mcb_part( 'cards/card-' . $vm_card, array( 'post_id' => $vm_id ) );
