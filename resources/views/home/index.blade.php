<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Ecommtoday</title>
    <link rel="stylesheet" href="{{url('vendor/polaris/styles.css')}}"/>
    <style>
        a {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }
    </style>

    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    <script>
        var AppBridge = window['app-bridge'];
        const config = {
            apiKey: "{{ $bridgeConfig['apiKey'] ?? '' }}",
            host: "{{ $bridgeConfig['host'] ?? '' }}",
            forceRedirect: true
        };
        const app = AppBridge.createApp(config);
    </script>
</head>
<body>
@php($statusText =  !$active ? 'Active' : 'Not active')
<div class="Polaris-Page Polaris-Page--fullWidth">
    <div class="Polaris-IndexTable">
        <div class="Polaris-Box"
             style="--pc-box-padding-block-end-xs:var(--p-space-4);--pc-box-padding-block-end-md:var(--p-space-5);--pc-box-padding-block-start-xs:var(--p-space-4);--pc-box-padding-block-start-md:var(--p-space-5);--pc-box-padding-inline-start-xs:var(--p-space-4);--pc-box-padding-inline-start-sm:var(--p-space-0);--pc-box-padding-inline-end-xs:var(--p-space-4);--pc-box-padding-inline-end-sm:var(--p-space-0);position:relative">
            <div class="Polaris-Page-Header--noBreadcrumbs Polaris-Page-Header--mediumTitle">
                <div class="Polaris-Page-Header__Row">
                    <div class="Polaris-Page-Header__TitleWrapper"><h1 class="Polaris-Header-Title">Ecommtoday</h1>
                    </div>
                    <div class="Polaris-Page-Header__RightAlign">
                        <div class="Polaris-Page-Header__Actions">
                            <div class="Polaris-Page-Header__SecondaryActionWrapper">
                                <div class="Polaris-Box Polaris-Box--printHidden">
                                    <span class="Polaris-Button__Content Polaris-Button-s">
                                        <a href="https://www.ecommtoday.com/shopify-faq" target="_blank" class="Polaris-Button">
                                            <span class="Polaris-Button__Text">FAQ</span>
                                        </a>
                                    </span>
                                </div>
                            </div>

                            <div class="Polaris-Page-Header__PrimaryActionWrapper">
                                <div class="Polaris-Box Polaris-Box--printHidden">
                                    <span class="Polaris-Button__Content Polaris-Button-s">
                                        <a href="mailto:apps@ecommtoday.com" target="_blank" class="Polaris-Button Polaris-Button--primary">
                                            <span class="Polaris-Button__Text">Contact Us</span>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="Polaris-IndexTable__IndexTableWrapper Polaris-Scrollable--vertical Polaris-Scrollable--hasBottomShadow">
            <div class="Polaris-IndexTable__StickyTable" role="presentation">
                <div>
                    <div>
                    </div>
                    <div>
                        <div class="Polaris-IndexTable__StickyTableHeader">
                            <div class="Polaris-IndexTable__StickyTableColumnHeader">
                                <div class="Polaris-IndexTable__TableHeading" data-index-table-sticky-heading="true"style="min-width: 800px;">
                                    <div
                                        class="Polaris-LegacyStack Polaris-LegacyStack--spacingNone Polaris-LegacyStack--alignmentCenter Polaris-LegacyStack--noWrap">
                                        <div class="Polaris-LegacyStack__Item">
                                            <div class="Polaris-IndexTable--stickyTableHeadingSecondScrolling">
                                                Merchant name
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="Polaris-IndexTable-ScrollContainer">
                <table class="Polaris-IndexTable__Table Polaris-IndexTable__Table--sticky">
                    <thead>
                        <tr>
                            <th class="Polaris-IndexTable__TableHeading" data-index-table-sticky-heading="true">
                                Merchant name
                            </th>
                            <th class="Polaris-IndexTable__TableHeading">
                                <span class="Polaris-Text--root Polaris-Text--block" data-index-table-sticky-heading="true">
                                    Status
                                </span>
                            </th>
                            <th class="Polaris-IndexTable__TableHeading">
                                <span class="Polaris-Text--root Polaris-Text--block Polaris-Text--start" data-index-table-sticky-heading="true">
                                    Note
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="Polaris-IndexTable__TableRow">
                            <td class="Polaris-IndexTable__TableCell">
                                <span class="Polaris-Text--root Polaris-Text--bold">
                                    {{$shop}}
                                </span>
                            </td>
                            <td class="Polaris-IndexTable__TableCell">
                                <span class="{{!$active ? 'Polaris-Badge' : 'Polaris-Badge Polaris-Badge--statusSuccess'}}">
                                    {{!$active ? 'Not Active' : 'Active'}}
                                </span>
                            </td>
                            <td class="Polaris-IndexTable__TableCell">
                                <span class="Polaris-Text--root Polaris-Text--block Polaris-Text--start Polaris-Text" style="word-break: break-word;">
                                    {{!$active ? 'Unfortunately, your store has not been registered. Please contact Ecommtoday.' : 'Your store has been successfully registered with Ecommtoday!'}}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
