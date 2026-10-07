export const nfts = [
  { id: 1, title: 'Glow Cat #88', price: 4102, owner: 'NeonTapper', hue: 190 },
  { id: 2, title: 'Ether Bot #42', price: 92400, owner: 'NeonTapper', hue: 210 },
  { id: 3, title: 'Psy Head #09', price: 18500, owner: 'CyberApe', hue: 260 },
  { id: 4, title: 'Hyper Ring', price: 1200, owner: 'HyperClicker', hue: 170 },
]

export const profile = {
  name: 'HyperClicker', handle: '@cyberTapExpert',
  stats: [
    { label: 'KOPĀ $BORIS', value: '1,4 milj. $BORIS' },
    { label: 'PIEDEROŠIE NFT', value: '14 gab.' },
  ],
  earned: [{ id: 11, title: 'CyberApe #04', price: 3520, hue: 280 }, { id: 12, title: 'Glow Cat #88', price: 1240, hue: 190 }],
  purchased: [{ id: 13, title: 'Hyper Ring', price: 1200, hue: 170 }],
}

export const fmt = (n) => n.toLocaleString('lv-LV')
