// ─── Premium Asset Catalog ────────────────────────────────────────────────────
export const PREMIUM_ASSETS = {
  backgrounds: [
    { key: 'premium-bg-1',          path: '/assets/premium/background.png',                    label: 'Background 1' },
    { key: 'premium-bg-atas',       path: '/assets/premium/background-atas.png',               label: 'Bg Atas' },
    { key: 'premium-bg-bawah',      path: '/assets/premium/background-bawah.png',              label: 'Bg Bawah' },
    { key: 'premium-bg-transparan', path: '/assets/premium/background-transparan.png',         label: 'Transparan' },
    { key: 'premium-canvas',        path: '/assets/premium/canvas-bg.png',                     label: 'Canvas' },
    { key: 'premium-canvas-nama',   path: '/assets/premium/canvas-nama.png',                   label: 'Canvas Nama' },
    { key: 'premium-white',         path: '/assets/premium/white-bg.png',                      label: 'White' },
    { key: 'premium-white-nama',    path: '/assets/premium/white-bg-nama.png',                 label: 'White Nama' },
    { key: 'premium-only-bg',       path: '/assets/premium/only-background-transparan-bg.png', label: 'Only BG' },
  ],
  decorations: [
    { key: 'premium-atas',        path: '/assets/premium/transparan-atas.png',        label: 'Atas' },
    { key: 'premium-bawah',       path: '/assets/premium/transparan-bawah.png',       label: 'Bawah' },
    { key: 'premium-kiri-atas',   path: '/assets/premium/transparan-kiri-atas.png',   label: 'Kiri Atas' },
    { key: 'premium-kiri-bawah',  path: '/assets/premium/transparan-kiri-bawah.png',  label: 'Kiri Bawah' },
    { key: 'premium-kanan-atas',  path: '/assets/premium/transparan-kanan-atas.png',  label: 'Kanan Atas' },
    { key: 'premium-kanan-bawah', path: '/assets/premium/transparan-kanan-bawah.png', label: 'Kanan Bawah' },
  ],
  characters: [
    { key: 'premium-karakter',      path: '/assets/premium/only-karakter.png',        label: 'Karakter' },
    { key: 'premium-tulisan-1',     path: '/assets/premium/transparan-tulisan.png',   label: 'Tulisan 1' },
    { key: 'premium-tulisan-2',     path: '/assets/premium/transparan-tulisan-2.png', label: 'Tulisan 2' },
    { key: 'premium-bg-nama',       path: '/assets/premium/transparan-bg-nama.png',   label: 'BG Nama' },
    { key: 'premium-transparan-bg', path: '/assets/premium/transparan-bg.png',        label: 'Transparan BG' },
  ],
}

const PREMIUM_PATH_MAP = Object.values(PREMIUM_ASSETS)
  .flat()
  .reduce((acc, a) => { acc[a.key] = a.path; return acc }, {})

export const PRESET_DECORATIONS = {
  'preset-floral-1': '✿',
  'preset-floral-2': '❁',
  'preset-lantern-1': '۞',
  'preset-arch-1': '❋',
}

export const PRESET_CHARACTERS = {
  'preset-couple-1': '🕌',
  'preset-couple-2': '💍',
  'preset-couple-3': '❤',
}

function makeSlot(x, y, size) {
  return { type: 'none', url: null, uploaded_url: null, animation: 'none', x: x, y: y, size: size || 180, opacity: 90 }
}

export function resolveBuilder(themeConfig) {
  if (!themeConfig) themeConfig = {}
  return themeConfig.builder || {
    permissions: {},
    content: {
      font_family: 'font-sans',
      palette: {
        primary: '#065f46',
        secondary: '#d4af37',
        background: '#fdf8f0',
        text: '#1f2937',
      },
      background_image: { type: 'none', url: null, uploaded_url: null },
      cover_background_image: { type: 'none', url: null, uploaded_url: null },
      cover_decorations: {
        top:    makeSlot(50,  5, 200),
        bottom: makeSlot(50, 90, 200),
        left:   makeSlot( 5, 50, 150),
        right:  makeSlot(95, 50, 150),
      },
      content_decorations: {
        top:    makeSlot(50,  5, 200),
        bottom: makeSlot(50, 90, 200),
        left:   makeSlot( 5, 50, 150),
        right:  makeSlot(95, 50, 150),
      },
      character_image: { type: 'preset-couple-1', url: null, uploaded_url: null, size: 120, x: 50, y: 35 },
      custom_text: {
        cover_intro: null,
        cover_button_label: 'Buka Undangan',
        closing_note: null,
      },
      music_url: null,
      music_uploaded_url: null,
      music_autoplay: false,
      blocks: [
        { id: 'ayat',      label: 'Ayat',     enabled: true },
        { id: 'countdown', label: 'Countdown', enabled: true },
        { id: 'mempelai',  label: 'Mempelai',  enabled: true },
        { id: 'acara',     label: 'Acara',     enabled: true },
        { id: 'lokasi',    label: 'Lokasi',    enabled: true },
        { id: 'gift',      label: 'Gift',      enabled: true },
        { id: 'rsvp',      label: 'RSVP',      enabled: true },
        { id: 'doa',       label: 'Doa',       enabled: true },
        { id: 'penutup',   label: 'Penutup',   enabled: true },
      ],
    },
  }
}

export function resolveAssetUrl(item) {
  if (!item) return null
  if (item.uploaded_url) return item.uploaded_url
  if (item.url) return item.url
  if (item.type && PREMIUM_PATH_MAP[item.type]) return PREMIUM_PATH_MAP[item.type]
  if (item.type && item.type.startsWith('/assets/premium/')) return item.type
  return null
}

export function resolveDecorationVisual(decoration) {
  if (!decoration) return { type: 'none', value: null }
  var url = resolveAssetUrl(decoration)
  if (url) return { type: 'image', value: url }
  var symbol = PRESET_DECORATIONS[decoration.type]
  if (symbol) return { type: 'symbol', value: symbol }
  return { type: 'none', value: null }
}

export function resolveCharacterVisual(character) {
  if (!character) return { type: 'symbol', value: '🕌' }
  var url = resolveAssetUrl(character)
  if (url) return { type: 'image', value: url }
  return { type: 'symbol', value: PRESET_CHARACTERS[character.type] || '🕌' }
}

export function resolveBackgroundVisual(bg) {
  if (!bg) return { type: 'none', value: null }
  var url = resolveAssetUrl(bg)
  if (url) return { type: 'image', value: url }
  return { type: 'none', value: null }
}

export function animationClass(animation) {
  switch (animation) {
    case 'float':     return 'animate-[float_4s_ease-in-out_infinite]'
    case 'pulse':     return 'animate-pulse'
    case 'spin-slow': return 'animate-[spin_8s_linear_infinite]'
    default:          return ''
  }
}

export function isBlockEnabled(builder, blockId) {
  var blocks = (builder && builder.content && builder.content.blocks) || []
  var block = blocks.find(function(item) { return item.id === blockId })
  return block ? block.enabled !== false : true
}

export function enabledBlocks(builder) {
  var blocks = (builder && builder.content && builder.content.blocks) || []
  return blocks.filter(function(item) { return item.enabled !== false })
}
