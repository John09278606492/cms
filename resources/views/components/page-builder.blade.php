@props(['blocks' => [], 'page' => null])
@php
    $blocks = is_array($blocks) ? $blocks : [];

    $spaceMap = [
        'py' => ['sm' => 'py-6', 'md' => 'py-10', 'lg' => 'py-16', 'xl' => 'py-24'],
        'px' => ['sm' => 'px-4', 'md' => 'px-8', 'lg' => 'px-12', 'xl' => 'px-16'],
        'mt' => ['sm' => 'mt-6', 'md' => 'mt-10', 'lg' => 'mt-16', 'xl' => 'mt-24'],
        'mb' => ['sm' => 'mb-6', 'md' => 'mb-10', 'lg' => 'mb-16', 'xl' => 'mb-24'],
    ];
@endphp
<div {{ $attributes->merge(['class' => 'flex flex-col gap-10']) }}>
    @foreach ($blocks as $block)
        @php
            $type = $block['type'] ?? null;
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            $bg = $data['_bg'] ?? null;
            $gradTo = $data['_grad_to'] ?? null;
            $bgImage = $data['_bg_image'] ?? null;
            $overlay = $data['_overlay'] ?? 'none';
            $pad = $data['_pad'] ?? 'none';
            $padx = $data['_padx'] ?? 'none';
            $mt = $data['_mt'] ?? 'none';
            $mb = $data['_mb'] ?? 'none';
            $width = $data['_width'] ?? 'default';
            $align = $data['_align'] ?? null;
            $textColor = $data['_text_color'] ?? null;
            $fontSize = $data['_font_size'] ?? 'default';
            $fontWeight = $data['_font_weight'] ?? 'default';
            $radius = $data['_radius'] ?? '';
            $shadow = $data['_shadow'] ?? 'none';
            $borderWidth = $data['_border_width'] ?? 'none';
            $borderColor = $data['_border_color'] ?? null;

            $hasImage = ! empty($bgImage);
            $hasBackground = ! empty($bg) || ! empty($gradTo) || $hasImage;

            // Box classes (literal so Tailwind can see them).
            $boxClasses = [];
            $boxClasses[] = $spaceMap['py'][$pad] ?? '';
            $boxClasses[] = $spaceMap['px'][$padx] ?? '';
            // When a background/border is present but no explicit horizontal
            // padding, give the content some breathing room.
            if ($hasBackground && ($spaceMap['px'][$padx] ?? '') === '') {
                $boxClasses[] = 'px-6';
            }
            $boxClasses[] = match ($align) { 'center' => 'text-center', 'right' => 'text-right', 'left' => 'text-left', default => '' };
            $boxClasses[] = match ($fontSize) { 'sm' => 'text-sm', 'base' => 'text-base', 'lg' => 'text-lg', 'xl' => 'text-xl', default => '' };
            $boxClasses[] = match ($fontWeight) { 'normal' => 'font-normal', 'medium' => 'font-medium', 'semibold' => 'font-semibold', 'bold' => 'font-bold', default => '' };
            // Corner radius: a custom value (any CSS, incl. per-corner like
            // "12px 0 12px 0") wins; otherwise a preset. 'none' is honoured
            // explicitly so a backgrounded box can have square edges.
            $radiusCustom = trim((string) ($data['_radius_custom'] ?? ''));
            if ($radiusCustom === '') {
                $boxClasses[] = match ($radius) {
                    'none' => '',
                    'sm' => 'rounded-lg',
                    'md' => 'rounded-xl',
                    'lg' => 'rounded-2xl',
                    'xl' => 'rounded-3xl',
                    'full' => 'rounded-full',
                    default => ($hasBackground ? 'rounded-2xl' : ''),
                };
            }
            $boxClasses[] = match ($shadow) { 'sm' => 'shadow', 'md' => 'shadow-md', 'lg' => 'shadow-lg', 'xl' => 'shadow-2xl', default => '' };
            if ($hasImage) {
                $boxClasses[] = 'bg-cover bg-center';
            }
            // When a wrapper text colour is chosen, make common text descendants
            // inherit it so it actually wins over each block's own colour class.
            if (! empty($textColor)) {
                $boxClasses[] = '[&_h1]:text-inherit [&_h2]:text-inherit [&_h3]:text-inherit [&_h4]:text-inherit [&_p]:text-inherit [&_a]:text-inherit [&_li]:text-inherit [&_span]:text-inherit';
            }

            // Inline styles for arbitrary colours / images.
            $styles = [];
            if ($hasImage) {
                $imgUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($bgImage);
                $scrim = match ($overlay) {
                    'dark' => 'linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), ',
                    'light' => 'linear-gradient(rgba(255,255,255,0.6), rgba(255,255,255,0.6)), ',
                    default => '',
                };
                $styles[] = "background-image: {$scrim}url('{$imgUrl}')";
            } elseif (! empty($gradTo) && ! empty($bg)) {
                $styles[] = "background-image: linear-gradient(135deg, {$bg}, {$gradTo})";
            } elseif (! empty($bg)) {
                $styles[] = "background-color: {$bg}";
            }
            if (! empty($textColor)) {
                $styles[] = "color: {$textColor}";
            }
            if ($borderWidth !== 'none' && ! empty($borderColor)) {
                $styles[] = "border: {$borderWidth}px solid {$borderColor}";
            }
            if ($radiusCustom !== '') {
                $styles[] = "border-radius: {$radiusCustom}";
            }

            // Size controls. Width always gets max-width:100% so a fixed px width
            // still shrinks to fit small screens rather than overflowing.
            $sizeW = trim((string) ($data['_w'] ?? ''));
            $sizeMinH = trim((string) ($data['_minh'] ?? ''));
            $sizeSelf = $data['_self'] ?? null;
            if ($sizeW !== '') {
                $styles[] = "width: {$sizeW}";
                $styles[] = 'max-width: 100%';
                $margin = match ($sizeSelf) {
                    'center' => 'margin-left: auto; margin-right: auto',
                    'right' => 'margin-left: auto',
                    'left' => 'margin-right: auto',
                    default => '',
                };
                if ($margin !== '') {
                    $styles[] = $margin;
                }
            }
            if ($sizeMinH !== '') {
                $styles[] = "min-height: {$sizeMinH}";
            }

            // Offset / overlap: translate keeps the element in flow (siblings
            // don't shift) so it can overlap neighbours; z-index layers it.
            $offX = trim((string) ($data['_offset_x'] ?? ''));
            $offY = trim((string) ($data['_offset_y'] ?? ''));
            if ($offX !== '' || $offY !== '') {
                $styles[] = 'transform: translate(' . ($offX !== '' ? $offX : '0') . ', ' . ($offY !== '' ? $offY : '0') . ')';
            }
            $zIndex = trim((string) ($data['_z'] ?? ''));
            if ($zIndex !== '') {
                $styles[] = 'position: relative';
                $styles[] = "z-index: {$zIndex}";
            }

            $anim = $data['_anim'] ?? 'none';
            $hideMobile = ! empty($data['_hide_mobile']);
            $hideTablet = ! empty($data['_hide_tablet']);
            $hideDesktop = ! empty($data['_hide_desktop']);

            // Outer wrapper margins, animation and responsive visibility.
            $wrapClasses = [];
            $wrapClasses[] = $spaceMap['mt'][$mt] ?? '';
            $wrapClasses[] = $spaceMap['mb'][$mb] ?? '';
            if ($anim !== 'none' && $anim !== null) {
                // pb-anim* are custom classes defined in app.css; they only hide
                // the element once JS has marked the document ready, so previews
                // and no-JS visitors still see everything.
                $wrapClasses[] = 'pb-anim pb-anim-' . $anim;
            }
            if ($hideMobile) {
                $wrapClasses[] = 'max-md:hidden';
            }
            if ($hideTablet) {
                $wrapClasses[] = 'md:max-lg:hidden';
            }
            if ($hideDesktop) {
                $wrapClasses[] = 'lg:hidden';
            }

            $widthClass = match ($width) {
                'narrow' => 'mx-auto max-w-2xl',
                'wide' => 'mx-auto max-w-5xl',
                'full' => 'max-w-none',
                default => '',
            };

            // Per-device overrides need real media queries, so emit a tiny scoped
            // <style> targeting a unique class on this element. Tablet ≤1023px,
            // mobile ≤767px.
            $wTablet = trim((string) ($data['_w_tablet'] ?? ''));
            $wMobile = trim((string) ($data['_w_mobile'] ?? ''));
            $alignMobile = $data['_align_mobile'] ?? '';
            $responsiveStyle = '';
            $rules = '';
            if ($wTablet !== '') {
                $rules .= "@media(max-width:1023px){.{cls}{width:{$wTablet};max-width:100%}}";
            }
            if ($wMobile !== '') {
                $rules .= "@media(max-width:767px){.{cls}{width:{$wMobile};max-width:100%}}";
            }
            if (in_array($alignMobile, ['left', 'center', 'right'], true)) {
                $rules .= "@media(max-width:767px){.{cls}{text-align:{$alignMobile}}}";
            }
            if ($rules !== '') {
                $elClass = 'pb-el-' . \Illuminate\Support\Str::random(8);
                $boxClasses[] = $elClass;
                $responsiveStyle = '<style>' . str_replace('{cls}', $elClass, $rules) . '</style>';
            }

            $boxClass = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($boxClasses))));
            $wrapClass = trim(implode(' ', array_filter($wrapClasses)));
            $styleAttr = implode('; ', $styles);
            $hasBox = $boxClass !== '' || $styleAttr !== '';
        @endphp
        @if ($type && view()->exists('page-builder.blocks.' . $type))
            @if ($hasBox || $wrapClass !== '')
                <div @class([$wrapClass => $wrapClass !== ''])>
                    {!! $responsiveStyle !!}
                    <div class="{{ $boxClass }}" @if ($styleAttr !== '') style="{{ $styleAttr }}" @endif>
                        <div class="{{ $widthClass }}">
                            @include('page-builder.blocks.' . $type, $data + ['__page' => $page])
                        </div>
                    </div>
                </div>
            @else
                @include('page-builder.blocks.' . $type, $data + ['__page' => $page])
            @endif
        @endif
    @endforeach
</div>
