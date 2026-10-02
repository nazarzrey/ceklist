# Result Log

## 2026-09-19 â€” Ollama 8B thinking mode

**Issue:** Model 8B (Qwen3) terus nulis thinking sebelum jawab; 7B (non-reasoning) langsung jawab.

**Sebab:** Qwen3 = reasoning model, thinking default ON. 7B lama (Llama 3 dkk) tidak punya mode thinking.

**Solusi matikan thinking:**
- CLI: `ollama run qwen3:8b --think=false`
- Sesi interaktif: `/set nothink`
- API: tambah `"think": false` di request body

Referensi: https://docs.ollama.com/capabilities/thinking

## Tanya: Matiin thinking turunkan kualitas?

**Jawab:** Bergantung. Non-thinking mode Qwen3 masih bagus utk tugas sederhana (terjemahan, ringkas, kode). Untuk soal logika/aritmatika rumit, quality turun karena kehilangan reasoning berlapis. Qwen3 dirancang dual-mode—sama model, quality tetep mampu, tapi kedalaman analisis berkurang di non-thinking.


## 2026-09-19 — Rekomendasi model Ollama utk PC ini

**Spek:** RTX 4060 8GB VRAM, 32GB RAM.

**Paling pas:** qwen3:8b (~5GB Q4) — all-round, muat penuh di VRAM.

Alternatif:
- qwen3-coder:8b (~5GB) — fokus koding/database
- deepseek-r1:7b (~5GB) — reasoning/logika kuat
- qwen3:4b (~3GB) — ringan/cepat

14B+ gak muat VRAM 8GB (split ke RAM, lambat).



## 2026-09-19 — Model terbaik per bahasa proyek

Semua (Python, C++, Java, Arduino, PHP, web modern, JavaScript, VB.NET): **qwen3-coder:8b** (satu model cukup utk semua). VB.NET fallback: qwen3:8b kalau syntax aneh.

Upgrade opsional: qwen2.5-coder:14b (~9GB, offload ke RAM, lebih bagus tapi lambat).

Setup: qwen3-coder:8b (default) + qwen3:8b (fallback non-code).



## 2026-09-19 — Model pengetahuan umum

Terbaik utk VRAM 8GB: **qwen3:8b** (aktifkan think utk pertanyaan analitis). Alternatif: gemma3:8b (reasoning lebih lemah).

Setup final: qwen3:8b (knowledge/umum) + qwen3-coder:8b (coding).



## 2026-09-19 — Pemetaan model utk mata kuliah

- RPL: qwen3:8b (teori/UML) + coder (code)
- Kerja Praktek: qwen3:8b
- IoT: qwen3-coder:8b (C/ESP32/Arduino)
- Pemrograman II: qwen3-coder:8b (OOP)
- Basis Data II: qwen3-coder:8b (SQL) + qwen3:8b (teori)
- Mobile Programming: qwen3-coder:8b (Kotlin/Java/Flutter)
- SPK: qwen3:8b think ON (AHP/SAW/TOPSIS matematis)
- Teknik Kompilasi: qwen3:8b think ON (automata) + coder (lexer/parser)

Pola: teori/matematika → qwen3:8b+think. Implementasi → qwen3-coder:8b.



## 2026-09-19 — Hermes-3 vs qwen3

Hermes-3 (Nous) = Llama-3.1-8B + tuning function calling/tool use. Pas utk agent yg manggil tool (search/DB). Cutoff 2024.

qwen3:8b = general + reasoning lebih kuat. Keduanya butuh wiring aplikasi utk akses internet (tool gak melekat di model).

