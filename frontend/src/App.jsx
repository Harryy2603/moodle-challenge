import { useState, useRef, useCallback } from 'react'
import './App.css'

const API_URL = '/api'

// ─── Utility ────────────────────────────────────────────────────────────────

async function callApi(file, dryRun) {
  const form = new FormData()
  form.append('csv_file', file)
  form.append('dry_run', dryRun ? 'true' : 'false')

  const res = await fetch(API_URL, { method: 'POST', body: form })
  const json = await res.json()

  if (!res.ok) throw new Error(json.error || `Server error ${res.status}`)
  return json
}

// ─── Sub-components ─────────────────────────────────────────────────────────

function UploadArea({ onFile, isDragging, setIsDragging }) {
  const inputRef = useRef(null)

  const handleDrop = useCallback(e => {
    e.preventDefault()
    setIsDragging(false)
    const file = e.dataTransfer.files?.[0]
    if (file) onFile(file)
  }, [onFile, setIsDragging])

  const handleDragOver = e => { e.preventDefault(); setIsDragging(true) }
  const handleDragLeave = () => setIsDragging(false)
  const handleChange = e => { if (e.target.files?.[0]) onFile(e.target.files[0]) }

  return (
    <div
      className={`upload-area ${isDragging ? 'upload-area--dragging' : ''}`}
      onDrop={handleDrop}
      onDragOver={handleDragOver}
      onDragLeave={handleDragLeave}
      onClick={() => inputRef.current?.click()}
      role="button"
      tabIndex={0}
      onKeyDown={e => e.key === 'Enter' && inputRef.current?.click()}
      aria-label="Upload CSV file"
    >
      <input
        ref={inputRef}
        type="file"
        accept=".csv,text/csv"
        onChange={handleChange}
        style={{ display: 'none' }}
      />
      <div className="upload-icon" aria-hidden="true">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
      </div>
      <p className="upload-primary">Drop your CSV file here</p>
      <p className="upload-secondary">or <span className="upload-link">browse to upload</span></p>
      <p className="upload-hint">Columns expected: name, surname, email</p>
    </div>
  )
}

function FileChip({ file, onClear }) {
  return (
    <div className="file-chip">
      <span className="file-chip__icon" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
      </span>
      <span className="file-chip__name">{file.name}</span>
      <span className="file-chip__size">{(file.size / 1024).toFixed(1)} KB</span>
      <button className="file-chip__clear" onClick={onClear} aria-label="Remove file">×</button>
    </div>
  )
}

function StatCard({ label, value, variant }) {
  return (
    <div className={`stat-card stat-card--${variant}`}>
      <span className="stat-card__value">{value}</span>
      <span className="stat-card__label">{label}</span>
    </div>
  )
}

function StatusBadge({ record }) {
  if (record.isValid) {
    return <span className="badge badge--valid">Valid</span>
  }
  return (
    <span className="badge badge--error" title={record.errors.join(', ')}>
      {record.errors[0] || 'Invalid'}
    </span>
  )
}

function PreviewTable({ records }) {
  const [filter, setFilter] = useState('all')

  const filtered = records.filter(r => {
    if (filter === 'valid') return r.isValid
    if (filter === 'invalid') return !r.isValid
    return true
  })

  return (
    <div className="preview-table-wrap">
      <div className="table-toolbar">
        <span className="table-toolbar__count">{filtered.length} records</span>
        <div className="filter-tabs" role="tablist">
          {['all', 'valid', 'invalid'].map(f => (
            <button
              key={f}
              role="tab"
              aria-selected={filter === f}
              className={`filter-tab ${filter === f ? 'filter-tab--active' : ''}`}
              onClick={() => setFilter(f)}
            >
              {f.charAt(0).toUpperCase() + f.slice(1)}
            </button>
          ))}
        </div>
      </div>

      <div className="table-scroll">
        <table className="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Surname</th>
              <th>Email</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {filtered.length === 0 ? (
              <tr>
                <td colSpan={5} className="table-empty">No records match this filter.</td>
              </tr>
            ) : (
              filtered.map((r, i) => (
                <tr key={i} className={r.isValid ? '' : 'row--invalid'}>
                  <td className="row-num">{i + 1}</td>
                  <td>{r.name || <span className="cell-empty">—</span>}</td>
                  <td>{r.surname || <span className="cell-empty">—</span>}</td>
                  <td className="cell-email">{r.email || <span className="cell-empty">—</span>}</td>
                  <td><StatusBadge record={r} /></td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}

function Spinner() {
  return (
    <span className="spinner" role="status" aria-label="Loading">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
      </svg>
    </span>
  )
}

function ErrorBanner({ message, onDismiss }) {
  return (
    <div className="error-banner" role="alert">
      <span className="error-banner__icon" aria-hidden="true">⚠</span>
      <span className="error-banner__msg">{message}</span>
      <button className="error-banner__close" onClick={onDismiss} aria-label="Dismiss error">×</button>
    </div>
  )
}

function SuccessScreen({ result, onReset }) {
  return (
    <div className="success-screen">
      <div className="success-icon" aria-hidden="true">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="9 12 11 14 15 10"/>
        </svg>
      </div>
      <h2 className="success-title">Import complete</h2>
      <p className="success-body">
        <strong>{result.total_valid}</strong> user{result.total_valid !== 1 ? 's' : ''} imported successfully.
        {result.total_invalid > 0 && (
          <> <span className="success-skipped">{result.total_invalid} invalid record{result.total_invalid !== 1 ? 's' : ''} skipped.</span></>
        )}
      </p>
      <button className="btn btn--primary" onClick={onReset}>Import another file</button>
    </div>
  )
}

// ─── Main App ────────────────────────────────────────────────────────────────

const STAGE = { IDLE: 'idle', PREVIEWING: 'previewing', PREVIEW_DONE: 'preview_done', IMPORTING: 'importing', DONE: 'done' }

export default function App() {
  const [stage, setStage] = useState(STAGE.IDLE)
  const [file, setFile] = useState(null)
  const [isDragging, setIsDragging] = useState(false)
  const [preview, setPreview] = useState(null)
  const [importResult, setImportResult] = useState(null)
  const [error, setError] = useState(null)

  const handleFile = file => {
    setFile(file)
    setPreview(null)
    setError(null)
    setStage(STAGE.IDLE)
  }

  const handleClearFile = () => {
    setFile(null)
    setPreview(null)
    setError(null)
    setStage(STAGE.IDLE)
  }

  const handlePreview = async () => {
    setError(null)
    setStage(STAGE.PREVIEWING)
    try {
      const data = await callApi(file, true)
      setPreview(data)
      setStage(STAGE.PREVIEW_DONE)
    } catch (e) {
      setError(e.message || 'Failed to reach the server. Is the PHP backend running?')
      setStage(STAGE.IDLE)
    }
  }

  const handleImport = async () => {
    setError(null)
    setStage(STAGE.IMPORTING)
    try {
      const data = await callApi(file, false)
      setImportResult(data)
      setStage(STAGE.DONE)
    } catch (e) {
      setError(e.message || 'Import failed. Please try again.')
      setStage(STAGE.PREVIEW_DONE)
    }
  }

  const handleReset = () => {
    setFile(null)
    setPreview(null)
    setImportResult(null)
    setError(null)
    setStage(STAGE.IDLE)
  }

  const isLoading = stage === STAGE.PREVIEWING || stage === STAGE.IMPORTING

  // ── Done screen
  if (stage === STAGE.DONE && importResult) {
    return (
      <div className="app">
        <Header />
        <main className="main">
          <SuccessScreen result={importResult} onReset={handleReset} />
        </main>
      </div>
    )
  }

  return (
    <div className="app">
      <Header />
      <main className="main">

        {/* Step indicator */}
        <StepIndicator stage={stage} hasFile={!!file} />

        {/* Error */}
        {error && <ErrorBanner message={error} onDismiss={() => setError(null)} />}

        {/* Upload zone */}
        <section className="section">
          {!file ? (
            <UploadArea
              onFile={handleFile}
              isDragging={isDragging}
              setIsDragging={setIsDragging}
            />
          ) : (
            <div className="file-ready">
              <FileChip file={file} onClear={handleClearFile} />
              {stage !== STAGE.PREVIEW_DONE && (
                <button
                  className="btn btn--primary btn--lg"
                  onClick={handlePreview}
                  disabled={isLoading}
                >
                  {stage === STAGE.PREVIEWING ? <><Spinner /> Parsing file…</> : 'Preview data'}
                </button>
              )}
            </div>
          )}
        </section>

        {/* Preview results */}
        {preview && stage === STAGE.PREVIEW_DONE && (
          <section className="section section--preview">
            <div className="section-header">
              <h2 className="section-title">Preview</h2>
              <button className="btn btn--ghost btn--sm" onClick={handleClearFile}>
                Change file
              </button>
            </div>

            {/* Summary cards */}
            <div className="stats-row">
              <StatCard label="Total found" value={preview.total_processed} variant="neutral" />
              <StatCard label="Valid" value={preview.total_valid} variant="valid" />
              <StatCard label="Invalid" value={preview.total_invalid} variant="invalid" />
            </div>

            {/* Table */}
            <PreviewTable records={preview.records} />

            {/* Import CTA */}
            <div className="import-bar">
              {preview.total_valid > 0 ? (
                <>
                  <p className="import-bar__hint">
                    Only valid records will be inserted. Invalid records are skipped.
                  </p>
                  <button
                    className="btn btn--primary btn--lg"
                    onClick={handleImport}
                    disabled={isLoading}
                  >
                    {stage === STAGE.IMPORTING
                      ? <><Spinner /> Importing…</>
                      : `Import ${preview.total_valid} valid user${preview.total_valid !== 1 ? 's' : ''}`
                    }
                  </button>
                </>
              ) : (
                <p className="import-bar__hint import-bar__hint--warn">
                  No valid records to import. Fix the CSV and try again.
                </p>
              )}
            </div>
          </section>
        )}
      </main>
    </div>
  )
}

// ─── Header ──────────────────────────────────────────────────────────────────

function Header() {
  return (
    <header className="header">
      <div className="header__inner">
        <div className="header__brand">
          <span className="header__logo" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <ellipse cx="12" cy="5" rx="9" ry="3"/>
              <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
              <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
            </svg>
          </span>
          <span className="header__name">User Import</span>
        </div>
        <span className="header__badge">Moodle Challenge</span>
      </div>
    </header>
  )
}

// ─── Step Indicator ──────────────────────────────────────────────────────────

function StepIndicator({ stage, hasFile }) {
  const steps = ['Upload', 'Preview', 'Import']

  const activeIndex = (() => {
    if (stage === STAGE.PREVIEWING || stage === STAGE.PREVIEW_DONE) return 1
    if (stage === STAGE.IMPORTING || stage === STAGE.DONE) return 2
    return 0
  })()

  return (
    <div className="steps" aria-label="Progress">
      {steps.map((label, i) => (
        <div key={label} className={`step ${i < activeIndex ? 'step--done' : ''} ${i === activeIndex ? 'step--active' : ''}`}>
          <div className="step__circle">
            {i < activeIndex
              ? <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              : <span>{i + 1}</span>
            }
          </div>
          <span className="step__label">{label}</span>
          {i < steps.length - 1 && <div className="step__line" />}
        </div>
      ))}
    </div>
  )
}