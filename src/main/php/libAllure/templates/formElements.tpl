
<div>
{foreach from = $elements item = "element"}
	{if is_array($element)}
		{include file = "formElements.tpl" elements=$element}
	{else}
		{if $element->getType() eq 'ElementHidden'}
			<input type = "hidden" name = "{$element->getName()}" value = "{$element->getValue()}" />
		{elseif $element->getType() eq 'ElementHtml'}
			{$element->getValue()}
		{elseif $element->getType() eq 'ElementButton'}
			<button value = "{$form->getName()}" name = "{$element->getName()}" type = "submit">{$element->getCaption()}</button>
		{else}
			<fieldset>
				<label class = "{($element->isRequired()) ? 'required' : 'optional'}" for = "{$element->getName()}">{$element->getCaption()}</label>

				{$element->render()}

				{if $element->description ne ''}
				<p class = "description"><img src = "resources/images/icons/help.png" class = "imageIcon" alt = "Form element help" />{$element->description}</p>
				{/if}

				{if !empty($suggestedValues)}
				<div>
					{foreach from = $suggestedValues key = sv item = caption}
						<span class = "dummyLink" onclick = "document.getElementById('{$element->getName()}').value = '{$sv} '">{$caption}</span>';
					{/foreach}
				</div>
				{/if}

				{if $element->getValidationError() ne ''}
				<p class = "formValidationError">{$element->getValidationError()}</p>
				{/if}
			</fieldset>
		{/if}
	{/if}
{/foreach}

</div>
