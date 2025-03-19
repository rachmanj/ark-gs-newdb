<div class="col-12 col-sm-6 col-md-4">
    <div class="info-box mb-3 shadow-sm">
        <span
            class="info-box-icon {{ $reguler_daily['percentage'] * 100 <= 100 ? 'bg-gradient-success' : 'bg-gradient-danger' }} elevation-2">
            <i class="fas {{ $reguler_daily['percentage'] * 100 <= 100 ? 'fa-thumbs-up' : 'fa-frown' }} fa-lg"></i>
        </span>
        <div class="info-box-content">
            <span class="info-box-text text-muted">PO Sent vs Plant Budget</span>
            <div class="d-flex align-items-baseline">
                <h3 class="mb-0 mr-2 font-weight-bold">{{ number_format($reguler_daily['percentage'] * 100, 2) }}%</h3>
                <small class="{{ $reguler_daily['percentage'] * 100 <= 100 ? 'text-success' : 'text-danger' }}">
                    <i
                        class="fas {{ $reguler_daily['percentage'] * 100 <= 100 ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                    {{ $reguler_daily['percentage'] * 100 <= 100 ? 'On Budget' : 'Over Budget' }}
                </small>
            </div>
            <div class="progress progress-sm mt-2">
                <div class="progress-bar {{ $reguler_daily['percentage'] * 100 <= 100 ? 'bg-success' : 'bg-danger' }}"
                    style="width: {{ min($reguler_daily['percentage'] * 100, 100) }}%"></div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-md-4">
    <div class="info-box mb-3 shadow-sm">
        <span
            class="info-box-icon {{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'bg-gradient-success' : 'bg-gradient-danger' }} elevation-2">
            <i class="fas {{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'fa-thumbs-up' : 'fa-frown' }} fa-lg"></i>
        </span>
        <div class="info-box-content">
            <span class="info-box-text text-muted">PO Sent vs GRPO</span>
            <div class="d-flex align-items-baseline">
                <h3 class="mb-0 mr-2 font-weight-bold">{{ number_format($grpo_daily['total_percentage'] * 100, 2) }}%
                </h3>
                <small class="{{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'text-success' : 'text-danger' }}">
                    <i
                        class="fas {{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                    {{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'Good' : 'Attention Needed' }}
                </small>
            </div>
            <div class="progress progress-sm mt-2">
                <div class="progress-bar {{ $grpo_daily['total_percentage'] * 100 >= 80 ? 'bg-success' : 'bg-danger' }}"
                    style="width: {{ min($grpo_daily['total_percentage'] * 100, 100) }}%"></div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-sm-6 col-md-4">
    <div class="info-box mb-3 shadow-sm">
        <span
            class="info-box-icon {{ $npi_daily['total_percentage'] <= 1 ? 'bg-gradient-success' : 'bg-gradient-danger' }} elevation-2">
            <i class="fas {{ $npi_daily['total_percentage'] <= 1 ? 'fa-thumbs-up' : 'fa-frown' }} fa-lg"></i>
        </span>
        <div class="info-box-content">
            <span class="info-box-text text-muted">N P I</span>
            <div class="d-flex align-items-baseline">
                <h3 class="mb-0 mr-2 font-weight-bold">{{ number_format($npi_daily['total_percentage'], 2) }}</h3>
                <small class="{{ $npi_daily['total_percentage'] <= 1 ? 'text-success' : 'text-danger' }}">
                    <i
                        class="fas {{ $npi_daily['total_percentage'] <= 1 ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                    {{ $npi_daily['total_percentage'] <= 1 ? 'Good' : 'Needs Review' }}
                </small>
            </div>
            <div class="progress progress-sm mt-2">
                <div class="progress-bar {{ $npi_daily['total_percentage'] <= 1 ? 'bg-success' : 'bg-danger' }}"
                    style="width: {{ min($npi_daily['total_percentage'] * 100, 100) }}%"></div>
            </div>
        </div>
    </div>
</div>
