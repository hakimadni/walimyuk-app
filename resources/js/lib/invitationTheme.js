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

export const DEFAULT_SLOT_COORDS = {
  top_left:     { x: 0,   y: 0 },
  top_right:    { x: 100, y: 0 },
  bottom_left:  { x: 0,   y: 100 },
  bottom_right: { x: 100, y: 100 },
  top:          { x: 50,  y: 5 },
  bottom:       { x: 50,  y: 90 },
  left:         { x: 5,   y: 50 },
  right:        { x: 95,  y: 50 },
}

function makeSlot(x, y, size) {
  return { type: 'none', url: null, uploaded_url: null, animation: 'none', x: x, y: y, size: size || 140, opacity: 90 }
}

export function resolveBuilder(themeConfig) {
  if (!themeConfig) themeConfig = {}
  if (themeConfig.builder) {
    if (!themeConfig.builder.content) themeConfig.builder.content = {}
    if (!themeConfig.builder.content.couple_photo_frame) {
      themeConfig.builder.content.couple_photo_frame = 'circle'
    }
    const cd = themeConfig.builder.content.content_decorations || {}
    if (!cd.top_left) {
      cd.top_left = makeSlot(0, 0, 140)
      if (cd.left?.type && cd.left.type !== 'none') {
        cd.top_left.type = cd.left.type
        cd.top_left.url = cd.left.url
        cd.top_left.uploaded_url = cd.left.uploaded_url
      }
    }
    if (!cd.top_right) {
      cd.top_right = makeSlot(100, 0, 140)
      if (cd.right?.type && cd.right.type !== 'none') {
        cd.top_right.type = cd.right.type
        cd.top_right.url = cd.right.url
        cd.top_right.uploaded_url = cd.right.uploaded_url
      }
    }
    if (!cd.bottom_left) {
      cd.bottom_left = makeSlot(0, 100, 110)
    }
    if (!cd.bottom_right) {
      cd.bottom_right = makeSlot(100, 100, 110)
    }
    // Delete legacy slots so they are never accidentally rendered in content cards
    delete cd.top
    delete cd.bottom
    delete cd.left
    delete cd.right
    themeConfig.builder.content.content_decorations = cd
    return themeConfig.builder
  }
  return {
    permissions: {},
    content: {
      font_family: 'font-sans',
      cover_text_effect: 'none',
      couple_photo_frame: 'circle',
      cover_top_spacing: 84,
      palette: {
        primary: '#065f46',
        secondary: '#d4af37',
        background: '#fdf8f0',
        text: '#1f2937',
        guest_card_background: '#000000',
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
        top_left:     makeSlot(0,   0, 140),
        top_right:    makeSlot(100, 0, 140),
        bottom_left:  makeSlot(0, 100, 110),
        bottom_right: makeSlot(100, 100, 110),
      },
      character_image: { type: 'preset-couple-1', url: null, uploaded_url: null, size: 240, x: 50, y: 55 },
      custom_text: {
        cover_intro: null,
        cover_button_label: 'Buka Undangan',
        cover_hashtag: '#selamANYAuntukHAKIM',
        closing_note: null,
      },
      music_url: null,
      music_uploaded_url: null,
      music_autoplay: false,
      blocks: [
        { id: 'ayat',      label: 'Ayat',      enabled: true },
        { id: 'mempelai',  label: 'Mempelai',  enabled: true },
        { id: 'acara',     label: 'Acara',     enabled: true },
        { id: 'lokasi',    label: 'Lokasi',    enabled: true },
        { id: 'countdown', label: 'Countdown', enabled: true },
        { id: 'gift',      label: 'Gift',      enabled: true },
        { id: 'rsvp',      label: 'RSVP',      enabled: true },
        { id: 'doa',       label: 'Doa',       enabled: true },
        { id: 'penutup',   label: 'Penutup',   enabled: true },
      ],
    },
  }
}

export function normalizeStorageUrl(url) {
  if (!url || typeof url !== 'string') return null
  if (url.includes('/storage/')) {
    const idx = url.indexOf('/storage/')
    return url.substring(idx)
  }
  return url
}

export function resolveAssetUrl(item) {
  if (!item) return null
  var rawUrl = item.uploaded_url || item.url || (item.type && PREMIUM_PATH_MAP[item.type]) || (item.type && item.type.startsWith('/assets/premium/') ? item.type : null)
  return normalizeStorageUrl(rawUrl)
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
    case 'none':      return ''
    case 'pulse':     return 'animate-pulse'
    case 'spin-slow': return 'animate-[spin_8s_linear_infinite]'
    case 'float':
    default:          return 'anim-float'
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

export function getContentDecorationStyle(slot, deco, scale = 1) {
  if (!deco) return {}
  const rawX = deco.x !== undefined && deco.x !== null && deco.x !== '' ? +deco.x : (DEFAULT_SLOT_COORDS[slot]?.x ?? 0)
  const rawY = deco.y !== undefined && deco.y !== null && deco.y !== '' ? +deco.y : (DEFAULT_SLOT_COORDS[slot]?.y ?? 0)
  const defaultSize = (slot === 'bottom_left' || slot === 'bottom_right') ? 110 : 140
  const size = (+deco.size || defaultSize) * scale
  const opacity = (deco.opacity !== undefined && deco.opacity !== null ? +deco.opacity : 90) / 100

  const base = {
    width: `${Math.round(size)}px`,
    maxHeight: '38%',
    maxWidth: '38%',
    opacity,
    zIndex: 20,
    position: 'absolute',
    pointerEvents: 'none',
  }

  switch (slot) {
    case 'top_left':
      return {
        ...base,
        top: `${rawY}%`,
        left: `${rawX}%`,
        transform: 'translate(-20%, -20%)',
      }
    case 'top_right':
      return {
        ...base,
        top: `${rawY}%`,
        right: `${100 - rawX}%`,
        transform: 'translate(20%, -20%)',
      }
    case 'bottom_left':
      return {
        ...base,
        bottom: `${100 - rawY}%`,
        left: `${rawX}%`,
        transform: 'translate(-20%, 20%)',
      }
    case 'bottom_right':
      return {
        ...base,
        bottom: `${100 - rawY}%`,
        right: `${100 - rawX}%`,
        transform: 'translate(20%, 20%)',
      }
    default:
      return {
        ...base,
        top: `${rawY}%`,
        left: `${rawX}%`,
        transform: 'translate(-50%, -50%)',
      }
  }
}
