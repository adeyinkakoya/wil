<?php

namespace WilokeListingTools\Framework\Helpers;


class HTML {
	public static function renderLink($class='', $name='', $link='', $icon='', $linkID='', $includePreloader=false){
	    $class = 'wil-btn ' . $class;
	    $linkID = empty($linkID) ? uniqid('link_id') : $linkID;
		?>
		<a id="<?php echo esc_attr($linkID); ?>" class="<?php echo esc_attr(trim($class)); ?>" href="<?php echo esc_url($link); ?>"><?php if(!empty($icon)):?><i class="<?php echo esc_attr($icon); ?>"></i><?php endif; ?><?php echo esc_html($name); ?> <?php if ($includePreloader): ?> <div class="pill-loading_module__3LZ6v hidden"><div class="pill-loading_loader__3LOnT"></div></div><?php endif; ?></a>
		<?php
	}

	public static function reStyleText($number){
		$input = number_format($number);
		$input_count = substr_count($input, ',');

		if($input_count != '0'){
			if($input_count == '1'){
				$timeK = 1000;
				$beforeK = substr($input, 0, -4).'k';
			} else if($input_count == '2'){
				$timeK = 1000000;
				$beforeK = substr($input, 0, -8).'mil';
			} else if($input_count == '3'){
				$timeK = 100000000000;
				$beforeK = substr($input, 0,  -12).'bil';
			} else {
				return $input;
			}

			$afterK = floatval(($number-($beforeK*$timeK))/100);

			return empty($afterK) ? $beforeK : $beforeK . $afterK;
		} else {
			return $input;
		}
    }

	public static function renderTable($aColumnTitles, $aColumnValues, $tblClass=''){
        $tblClass = 'table-module__table wil-table-responsive-lg ' . $tblClass;
        $tblClass = trim($tblClass);
        ?>
        <table class="<?php echo esc_attr($tblClass); ?>">
            <thead>
                <tr>
                    <?php foreach ($aColumnTitles as $title) :  ?>
                        <th><?php echo esc_html($title); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
	                <?php foreach ($aColumnValues as $key => $value) :  ?>
                        <td data-th="<?php echo esc_attr($aColumnTitles[$key]); ?>" class="column-<?php echo esc_attr(strtolower($aColumnTitles[$key])); ?>"><?php echo esc_html($value); ?></td>
	                <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <?php
    }

    public static function renderPaymentButtons(){
	    $aGateways = GetWilokeSubmission::getAllGateways();
        $total = count($aGateways);

        $aPaymentData = array(
            'paypal' => array(
                'icon' => 'la la-cc-paypal',
                'bg'   => 'bg-color-paypal',
                'name' => esc_html__('PayPal', 'wiloke-listing-tools')
            ),
            'stripe' => array(
	            'icon' => 'la la-cc-stripe',
	            'bg'   => 'bg-color-stripe',
	            'name' => esc_html__('Stripe', 'wiloke-listing-tools')
            ),
            'banktransfer' => array(
	            'icon' => 'la la-money',
	            'bg'   => 'bg-color-banktransfer',
	            'name' => esc_html__('Direct Bank Transfer', 'wiloke-listing-tools')
            )
        );

        switch ($total){
            case 1:
                $itemClass = 'col-md-12 col-lg-12';
                break;
            case 2:
	            $itemClass = 'col-md-6 col-lg-6';
                break;
            default:
	            $itemClass = 'col-md-4 col-lg-4';
                break;
        }

        foreach ($aGateways as $gateway):
            ?>
            <div class="<?php echo esc_attr($itemClass); ?>">
                <!-- icon-box-2_module__AWd3Y wil-text-center bg-color-primary -->
                <div class="icon-box-2_module__AWd3Y wil-text-center <?php echo esc_attr($aPaymentData[$gateway]['bg']); ?>">
                    <a id="wilcity-proceed-with-<?php echo esc_attr($gateway); ?>" class="disable wilcity-gateway-box" href="#">
                        <div class="icon-box-2_icon__ZqobK">
                            <i class="<?php echo esc_attr($aPaymentData[$gateway]['icon']); ?>"></i>
                        </div>
                        <p class="icon-box-2_content__1J1Eb"><?php echo esc_html($aPaymentData[$gateway]['name']); ?></p>
                    </a>
                </div><!-- End / icon-box-2_module__AWd3Y wil-text-center bg-color-primary -->
            </div>
            <?php
        endforeach;
    }
}