@props(['src', 'alt' => '', 'class' => '', 'width' => null, 'height' => null, 'lazy' => true])

<img src="{{ $src }}" alt="{{ $alt }}"
    @if ($class) class="{{ $class }}" @endif
    @if ($width) width="{{ $width }}" @endif
    @if ($height) height="{{ $height }}" @endif
    @if ($lazy) loading="lazy" decoding="async" @endif
    onerror="this.onerror=null; this.src='data:image/svg+xml,{{ rawurlencode('<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'150\' height=\'150\' viewBox=\'0 0 150 150\'><rect fill=\'#f3f4f6\' width=\'100%\' height=\'100%\'/><text fill=\'#9ca3af\' font-family=\'sans-serif\' font-size=\'14\' x=\'50%\' y=\'50%\' text-anchor=\'middle\' dy=\'.3em\'>No Image</text></svg>') }}';" />
