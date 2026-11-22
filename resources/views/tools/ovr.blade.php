<x-layouts.app :title="$tool['name']" :meta-description="$tool['summary']">
    @include('tools._header')

    <div class="mx-auto max-w-4xl px-4 py-8 lg:px-8">
        <section class="rounded-3xl border border-line bg-surface p-5 sm:p-8" data-ovr-calculator>
            <script type="application/json" data-ovr-config>{!! json_encode($clientConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>

            <label class="grid gap-2 text-sm">
                <span class="font-semibold">Vị trí cầu thủ</span>
                <select class="rounded-xl border border-line bg-canvas px-4 py-3" data-ovr-position>
                    @foreach ($ovr['positions'] as $key => $position)
                        <option value="{{ $key }}">{{ $position['label'] }}</option>
                    @endforeach
                </select>
            </label>

            <p class="mt-4 text-xs text-muted">Đào tạo tối đa {{ $ovr['max_trained_stats'] }} chỉ số, mỗi chỉ số tối đa +{{ $ovr['max_per_stat'] }}. Đã dùng: <strong class="text-accent" data-ovr-used>0</strong>/{{ $ovr['max_trained_stats'] }}.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[480px] text-sm">
                    <thead class="bg-surface-2 text-xs uppercase tracking-widest text-muted">
                        <tr>
                            <th class="px-3 py-3 text-left">Chỉ số</th>
                            <th class="px-3 py-3">OVR</th>
                            <th class="px-3 py-3">Đào tạo thêm</th>
                        </tr>
                    </thead>
                    <tbody data-ovr-rows></tbody>
                </table>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-line bg-canvas p-5 text-center">
                    <p class="text-sm text-muted">OVR trước đào tạo</p>
                    <p class="mt-1 font-display text-3xl font-semibold" data-ovr-before>0.00</p>
                </div>
                <div class="rounded-2xl border border-primary/40 bg-primary/10 p-5 text-center">
                    <p class="text-sm text-muted">OVR sau đào tạo</p>
                    <p class="mt-1 font-display text-3xl font-semibold text-primary" data-ovr-after aria-live="polite">0.00</p>
                </div>
            </div>

            <p class="mt-4 text-xs text-muted">OVR = trung bình có trọng số của các chỉ số theo vị trí (theo công thức vị trí phổ biến của FIFA). Kết quả chưa tính cộng thêm từ nâng cấp thẻ hay team color.</p>
        </section>
    </div>
</x-layouts.app>
