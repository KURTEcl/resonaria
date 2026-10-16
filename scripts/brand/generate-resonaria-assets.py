"""Prepare Resonaria brand assets from the supplied vector logo."""
from argparse import ArgumentParser
from copy import deepcopy
from pathlib import Path
import re
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[2]
DEFAULT_SOURCE = Path.home() / 'Documents/Proyectos/Resonaria/logo.svg'
BRAND = ROOT / 'apps/web/public/brand'
DOCS = ROOT / 'docs/02-diseno-ux/assets/brand'
INK, TEAL, VIOLET, MUTED = '#12302d', '#0f766e', '#6a4cc0', '#566070'
WHITE, CANVAS = '#ffffff', '#f4f6f8'
NS = 'http://www.w3.org/2000/svg'
ET.register_namespace('', NS)

def tag(name): return f'{{{NS}}}{name}'
def set_color(element, color):
    style = element.get('style', '')
    style = re.sub(r'fill:\s*rgb\([^)]*\)', f'fill:{color}', style)
    style = re.sub(r'fill:\s*#[0-9a-fA-F]{3,8}', f'fill:{color}', style)
    element.set('style', style)

def recolor(root, mode):
    paths = root.findall('.//' + tag('path'))
    # Source ordering: bubble, seven wordmark letters, then the orange dot and white "a".
    if mode == 'brand':
        set_color(paths[0], TEAL)
        for p in paths[1:8]: set_color(p, INK)
        set_color(paths[8], CANVAS)
        for p in paths[9:]: set_color(p, WHITE)
    elif mode == 'white':
        for p in paths: set_color(p, WHITE)
        for rect in root.findall('.//' + tag('rect')): rect.set('style', 'fill:#ffffff')
    elif mode == 'mono':
        for p in paths: set_color(p, INK)
        for rect in root.findall('.//' + tag('rect')): rect.set('style', f'fill:{INK}')
    return root

def svg_text(root, viewbox, title):
    root.set('viewBox', viewbox)
    root.set('width', '100%'); root.set('height', '100%')
    title_el = root.find(tag('title'))
    if title_el is None:
        title_el = ET.Element(tag('title')); root.insert(0, title_el)
    title_el.text = title
    return ET.tostring(root, encoding='unicode') + '\n'

def main():
    parser = ArgumentParser()
    parser.add_argument('--source', type=Path, default=DEFAULT_SOURCE)
    args = parser.parse_args()
    if not args.source.is_file(): raise SystemExit(f'Logo source not found: {args.source}')
    original = ET.parse(args.source).getroot()
    BRAND.mkdir(parents=True, exist_ok=True); DOCS.mkdir(parents=True, exist_ok=True)
    assets = {}
    for name, mode, box, title in [
        ('logo.svg', 'brand', '0 25 867 240', 'Resonaria'),
        ('logo-blanco.svg', 'white', '0 25 867 240', 'Resonaria — versión blanca'),
        ('logo-monocromo.svg', 'mono', '0 25 867 240', 'Resonaria — monocromo'),
        ('logo-descriptor.svg', 'brand', '0 25 867 300', 'Resonaria — Actualidad · Análisis · Opinión'),
        ('logo-descriptor-blanco.svg', 'white', '0 25 867 300', 'Resonaria — Actualidad · Análisis · Opinión, versión blanca'),
    ]:
        root = recolor(deepcopy(original), mode)
        if name.startswith('logo-descriptor'):
            text = ET.SubElement(root, tag('text'), {'x':'12', 'y':'326', 'fill': WHITE if mode == 'white' else MUTED,
                'font-family':'Arial, sans-serif', 'font-size':'25', 'letter-spacing':'0.4'})
            text.text = 'Actualidad · Análisis · Opinión'
        assets[name] = svg_text(root, box, title)
    for name, mode, title in [
        ('isotipo.svg','brand','Resonaria — símbolo'),
        ('isotipo-blanco.svg','white','Resonaria — símbolo blanco'),
        ('isotipo-monocromo.svg','mono','Resonaria — símbolo monocromo'),
    ]:
        root = recolor(deepcopy(original), mode)
        assets[name] = svg_text(root, '660 20 207 245', title)
    # A separate wordmark without the speech bubble, for flexible layouts.
    root = recolor(deepcopy(original), 'brand')
    assets['logo-palabra.svg'] = svg_text(root, '0 72 660 140', 'Resonaria — palabra')
    # Square app icon with the separated symbol centered on the current canvas color.
    icon = ET.Element(tag('svg'), {'viewBox':'0 0 330 330'})
    ET.SubElement(icon, tag('title')).text = 'Resonaria'
    ET.SubElement(icon, tag('rect'), {'width':'330','height':'330','rx':'68','fill':CANVAS})
    nested = deepcopy(recolor(deepcopy(original), 'brand'))
    nested.set('x','49'); nested.set('y','31'); nested.set('width','232'); nested.set('height','268')
    nested.set('viewBox','660 20 207 245'); nested.set('preserveAspectRatio','xMidYMid meet')
    icon.append(nested)
    assets['icono.svg'] = ET.tostring(icon, encoding='unicode') + '\n'
    for name, content in assets.items():
        (BRAND / name).write_text(content)
    for name in ('logo.svg','logo-descriptor.svg','logo-palabra.svg','isotipo.svg','isotipo-blanco.svg','isotipo-monocromo.svg','icono.svg'):
        (DOCS / name).write_text(assets[name])
    (ROOT / 'apps/web/public/favicon.svg').write_text(assets['icono.svg'])
    print(f'Prepared {len(assets)} vector assets from {args.source}')

if __name__ == '__main__': main()
