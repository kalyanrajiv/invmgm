<?php
$url = $this->Url->build(array('action'=>'get-product-models'));
?>
<table>
								<tr>
									<td>
                                        <?php 	echo $this->Form->input('brand_id', ['options' => $brands,'rel'=>$url,'value' => $brand_id]);		?>
									</td>
									<td id="model-td">
									<?php 	echo $this->Form->input('model_id', ['options' => $ProductModels,'value' => $model_id]);		?>	
									</td>
								</tr>
							</table>