{{--
  Partial: categories/_icon_picker.blade.php
  Visual grid icon picker for category forms.
  Variables:
    $currentIcon — slug currently selected (string|null)
    $currentColor — hex color to preview icons (string|null)
--}}
@php
$currentIcon  = $currentIcon  ?? null;
$currentColor = $currentColor ?? null;

$sections = [
    'Comida & Compras'   => ['cart', 'restaurant', 'coffee'],
    'Transporte'         => ['car', 'bus', 'fuel', 'plane'],
    'Hogar & Servicios'  => ['home', 'bolt', 'tools', 'wifi'],
    'Salud'              => ['heart', 'pill'],
    'Educación'          => ['graduation', 'book'],
    'Entretenimiento'    => ['film', 'music', 'gamepad'],
    'Ropa & Cuidado'     => ['shirt', 'scissors'],
    'Mascotas & Viajes'  => ['paw', 'gift'],
    'Deporte & Bebé'     => ['dumbbell', 'baby'],
    'Ingresos & Finanzas'=> ['briefcase', 'chart', 'dollar', 'credit-card', 'percent'],
    'General'            => ['tag', 'other'],
];

$ICONS = [
  'cart'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.39c.51 0 .95.34 1.09.83l.38 1.44M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.22c1.12-2.3 2.1-4.68 2.87-7.13A60.5 60.5 0 0 0 7.08 7.5H3.75l-.53-2H2.25"/>',
  'restaurant' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.36 0-2.7.056-4.02.166C6.84 8.51 6 9.47 6 10.61v2.51m6-4.87c1.36 0 2.7.056 4.02.166C17.16 8.51 18 9.47 18 10.61v2.51M15 8.25v-1.5m-3 0V3.75m-3 4.5v-1.5M3.38 19.5h17.25c.62 0 1.12-.5 1.12-1.12V6.75c0-.62-.5-1.12-1.12-1.12H3.37c-.62 0-1.12.5-1.12 1.12v11.63c0 .62.5 1.12 1.13 1.12Z"/>',
  'coffee'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
  'car'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>',
  'bus'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h7.5M12 12.75H9.75m0 0v-3.375c0-.621.504-1.125 1.125-1.125H12m-2.25 3.375h3.375m0 0V9.375m0 3.375H15m-6 3.75v3.375c0 .621-.504 1.125-1.125 1.125H6.375A1.125 1.125 0 0 1 5.25 18.75v-3.375m6 0h2.25m0 0v3.375c0 .621.504 1.125 1.125 1.125h1.5c.621 0 1.125-.504 1.125-1.125v-3.375m-6 0h2.25"/>',
  'fuel'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0-3-3m3 3 3-3m-8.25 6a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"/>',
  'home'       => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
  'bolt'       => '<path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>',
  'tools'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>',
  'heart'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>',
  'pill'       => '<path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/>',
  'graduation' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>',
  'book'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>',
  'film'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m-17.25 0h17.25"/>',
  'music'      => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z"/>',
  'gamepad'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z"/>',
  'shirt'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>',
  'scissors'   => '<path stroke-linecap="round" stroke-linejoin="round" d="m7.848 8.25 1.536.887M7.848 8.25a3 3 0 1 1-5.196-3 3 3 0 0 1 5.196 3Zm1.536.887a2.165 2.165 0 0 1 1.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m0 0a2.166 2.166 0 0 1 1.083-1.838c.28-.14.54-.361.79-.626m-1.873 2.464-2.077 1.2m0 0a3 3 0 1 1-5.196 3 3 3 0 0 1 5.196-3Zm8.422-8.464c.655.297 1.029.975.86 1.62-.2.77-.704 1.413-1.423 1.747M21 9.75l-2.573 2.573m-2.573-2.573L21 9.75m-7.5 4.75 2.5-2.5M12 12.75l2.5-2.5"/>',
  'paw'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z"/>',
  'plane'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>',
  'gift'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>',
  'dumbbell'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m-12.75 0V7.5m0 4.5v4.5m10.5-4.5V7.5m0 4.5v4.5M3 7.5h3M3 16.5h3m12-9h3m-3 9h3"/>',
  'baby'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9 9.75h.008v.008H9V9.75Zm5.25 0h.008v.008h-.008V9.75Z"/>',
  'wifi'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z"/>',
  'briefcase'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z"/>',
  'chart'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
  'dollar'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
  'tag'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>',
  'credit-card'=> '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>',
  'percent'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6M6.75 6.75h.008v.008H6.75V6.75Zm10.5 10.5h.008v.008h-.008v-.008ZM6.75 18a.75.75 0 0 1 0-1.5.75.75 0 0 1 0 1.5Zm10.5-10.5a.75.75 0 0 1 0-1.5.75.75 0 0 1 0 1.5ZM6.75 9a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Zm10.5 10.5a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Z"/>',
  'other'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>',
];
@endphp

<style>
.icon-opt { display: inline-block; }
.icon-opt input { display: none; }
.icon-opt-inner {
    cursor: pointer;
    border-radius: 8px;
    border: 2px solid transparent;
    padding: 6px;
    transition: border-color 0.12s, background 0.12s, transform 0.1s;
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 40px;
    background: var(--surface2);
}
.icon-opt-inner:hover { border-color: rgba(255,255,255,0.12); transform: translateY(-1px); }
.icon-opt input:checked + .icon-opt-inner {
    border-color: var(--accent);
    background: rgba(99,102,241,0.12);
    box-shadow: 0 0 0 1px var(--accent);
}
</style>

<div style="margin-bottom: 24px;">
    <label class="form-label" style="margin-bottom: 10px; display: block;">
        Ícono <span style="color:var(--muted); font-weight:400;">(opcional)</span>
    </label>

    <input type="hidden" name="icon" id="icon-value" value="{{ $currentIcon ?? '' }}">

    <div id="icon-preview" style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:10px 12px;background:var(--surface2);border-radius:8px;border:1px solid var(--border);">
        <div id="icon-preview-chip" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.3);flex-shrink:0;">
            <svg id="icon-preview-svg" width="16" height="16" fill="none" stroke="#6366f1" stroke-width="1.6" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
            </svg>
        </div>
        <span id="icon-preview-label" style="font-size:13px;color:var(--muted);">Ninguno seleccionado</span>
        <button type="button" id="icon-clear-btn" onclick="clearIcon()" style="margin-left:auto;font-size:11px;color:var(--muted);background:none;border:none;cursor:pointer;padding:2px 6px;border-radius:4px;display:none;">✕ Quitar</button>
    </div>

    @foreach($sections as $sectionLabel => $icons)
    <div style="margin-bottom: 10px;">
        <div style="font-size: 10px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-weight: 600;">{{ $sectionLabel }}</div>
        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
            @foreach($icons as $slug)
            @php $isSelected = ($currentIcon === $slug); @endphp
            <label class="icon-opt" title="{{ $slug }}" onclick="selectIcon('{{ $slug }}')">
                <input type="radio" name="_icon_picker" value="{{ $slug }}" {{ $isSelected ? 'checked' : '' }}>
                <div class="icon-opt-inner {{ $isSelected ? 'selected' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="var(--muted)" stroke-width="1.6" viewBox="0 0 24 24">
                        {!! $ICONS[$slug] !!}
                    </svg>
                </div>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<script>
(function(){
const ICON_PATHS = {
    @foreach(array_keys($ICONS) as $slug)
    '{{ $slug }}': `{!! addslashes($ICONS[$slug]) !!}`,
    @endforeach
};

const ICON_LABELS = {
    cart:'Carrito / Compras', restaurant:'Restaurante', coffee:'Café / Cafetería',
    car:'Auto', bus:'Transporte público', fuel:'Combustible', plane:'Viajes / Vuelos',
    home:'Hogar / Casa', bolt:'Electricidad / Servicios', tools:'Mantenimiento',
    wifi:'Internet / Servicios digitales', heart:'Salud / Bienestar', pill:'Farmacia / Medicamentos',
    graduation:'Educación / Universidad', book:'Libros / Cursos',
    film:'Cine / Streaming', music:'Música', gamepad:'Videojuegos / Juegos',
    shirt:'Ropa / Indumentaria', scissors:'Peluquería / Estética',
    paw:'Mascotas', gift:'Regalos / Donaciones', dumbbell:'Deporte / Gimnasio',
    baby:'Bebé / Niños', briefcase:'Trabajo / Sueldo', chart:'Inversiones / Rendimientos',
    dollar:'Efectivo / Ingresos', 'credit-card':'Tarjeta de crédito', percent:'Intereses / Cuotas',
    tag:'General', other:'Otro',
};

const colorInput = document.getElementById('color-text') || document.getElementById('color-picker');

function getAccentColor() {
    if (colorInput && colorInput.value && /^#[0-9a-fA-F]{6}$/.test(colorInput.value)) {
        return colorInput.value;
    }
    return '#6366f1';
}

function hexToRgb(hex) {
    const h = hex.replace('#', '');
    return { r: parseInt(h.slice(0,2),16), g: parseInt(h.slice(2,4),16), b: parseInt(h.slice(4,6),16) };
}

window.selectIcon = function(slug) {
    document.getElementById('icon-value').value = slug;
    const color = getAccentColor();
    const rgb   = hexToRgb(color);
    const chip  = document.getElementById('icon-preview-chip');
    const svg   = document.getElementById('icon-preview-svg');
    const label = document.getElementById('icon-preview-label');
    const clearBtn = document.getElementById('icon-clear-btn');

    chip.style.background = `rgba(${rgb.r},${rgb.g},${rgb.b},0.14)`;
    chip.style.border     = `1px solid rgba(${rgb.r},${rgb.g},${rgb.b},0.35)`;
    svg.setAttribute('stroke', color);
    svg.innerHTML = ICON_PATHS[slug] || ICON_PATHS['other'];
    label.textContent = ICON_LABELS[slug] || slug;
    label.style.color = 'var(--text)';
    clearBtn.style.display = '';

    // Update all radio visuals
    document.querySelectorAll('.icon-opt input').forEach(r => {
        const inner = r.nextElementSibling;
        if (r.value === slug) {
            inner.style.borderColor = 'var(--accent)';
            inner.style.background  = 'rgba(99,102,241,0.12)';
            inner.querySelector('svg').setAttribute('stroke', color);
        } else {
            inner.style.borderColor = 'transparent';
            inner.style.background  = 'var(--surface2)';
            inner.querySelector('svg').setAttribute('stroke', 'var(--muted)');
        }
    });
};

window.clearIcon = function() {
    document.getElementById('icon-value').value = '';
    document.querySelectorAll('.icon-opt input').forEach(r => {
        r.checked = false;
        const inner = r.nextElementSibling;
        inner.style.borderColor = 'transparent';
        inner.style.background  = 'var(--surface2)';
        inner.querySelector('svg').setAttribute('stroke', 'var(--muted)');
    });
    document.getElementById('icon-preview-svg').innerHTML = ICON_PATHS['other'];
    document.getElementById('icon-preview-svg').setAttribute('stroke', '#6a6676');
    document.getElementById('icon-preview-chip').style.background = 'rgba(106,102,118,0.1)';
    document.getElementById('icon-preview-chip').style.border = '1px solid rgba(106,102,118,0.2)';
    document.getElementById('icon-preview-label').textContent = 'Ninguno seleccionado';
    document.getElementById('icon-preview-label').style.color = 'var(--muted)';
    document.getElementById('icon-clear-btn').style.display = 'none';
};

// Init with current value
const initial = '{{ $currentIcon ?? '' }}';
if (initial) { setTimeout(() => selectIcon(initial), 0); }

// Sync color changes to selected icon preview
if (colorInput) {
    colorInput.addEventListener('input', () => {
        const cur = document.getElementById('icon-value').value;
        if (cur) selectIcon(cur);
    });
}
})();
</script>
