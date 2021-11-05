<?php

namespace TYPO3\CMS\Dashboard\Widgets {
    if (!interface_exists(WidgetInterface::class, true)) {
        interface WidgetInterface
        {
            public function renderWidgetContent(): string;
        }
    }
    if (!interface_exists(AdditionalCssInterface::class, true)) {
        // @codingStandardsIgnoreStart
        interface AdditionalCssInterface
        {
            // @codingStandardsIgnoreEnd

            /**
             * @return string[]
             */
            public function getCssFiles(): array;
        }
    }
}
