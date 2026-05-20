import React from 'react';
import Svg, { Path, Circle, Line, Polyline, Rect, Polygon } from 'react-native-svg';
import { colors } from '../theme';

export type IconName =
  | 'home'
  | 'search'
  | 'bell'
  | 'user'
  | 'plus'
  | 'map-pin'
  | 'check'
  | 'x'
  | 'arrow-left'
  | 'arrow-right'
  | 'chevron-left'
  | 'chevron-right'
  | 'chevron-down'
  | 'chevron-up'
  | 'heart'
  | 'star'
  | 'bookmark'
  | 'filter'
  | 'bolt'
  | 'phone'
  | 'mail'
  | 'lock'
  | 'edit'
  | 'menu'
  | 'chart'
  | 'globe'
  | 'trash'
  | 'whatsapp'
  | 'camera'
  | 'cart'
  | 'message'
  | 'settings'
  | 'logout'
  | 'image'
  | 'clock'
  | 'compass'
  | 'shield';

type IconProps = {
  name: IconName;
  size?: number;
  color?: string;
  strokeWidth?: number;
  filled?: boolean;
};

const stroke = (color: string, width: number) => ({
  stroke: color,
  strokeWidth: width,
  strokeLinecap: 'round' as const,
  strokeLinejoin: 'round' as const,
  fill: 'none' as const,
});

export function Icon({ name, size = 16, color = colors.ink[950], strokeWidth = 2 }: IconProps) {
  const s = stroke(color, strokeWidth);

  switch (name) {
    case 'home':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M3 12l9-9 9 9" {...s} />
          <Path d="M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10" {...s} />
        </Svg>
      );
    case 'search':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Circle cx={11} cy={11} r={7} {...s} />
          <Line x1={21} y1={21} x2={16.65} y2={16.65} {...s} />
        </Svg>
      );
    case 'bell':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9" {...s} />
          <Path d="M13.73 21a2 2 0 01-3.46 0" {...s} />
        </Svg>
      );
    case 'user':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" {...s} />
          <Circle cx={12} cy={7} r={4} {...s} />
        </Svg>
      );
    case 'plus':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Line x1={12} y1={5} x2={12} y2={19} {...s} />
          <Line x1={5} y1={12} x2={19} y2={12} {...s} />
        </Svg>
      );
    case 'map-pin':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" {...s} />
          <Circle cx={12} cy={10} r={3} {...s} />
        </Svg>
      );
    case 'check':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polyline points="20 6 9 17 4 12" {...s} />
        </Svg>
      );
    case 'x':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Line x1={18} y1={6} x2={6} y2={18} {...s} />
          <Line x1={6} y1={6} x2={18} y2={18} {...s} />
        </Svg>
      );
    case 'arrow-left':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Line x1={19} y1={12} x2={5} y2={12} {...s} />
          <Polyline points="12 19 5 12 12 5" {...s} />
        </Svg>
      );
    case 'arrow-right':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Line x1={5} y1={12} x2={19} y2={12} {...s} />
          <Polyline points="12 5 19 12 12 19" {...s} />
        </Svg>
      );
    case 'chevron-left':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polyline points="15 18 9 12 15 6" {...s} />
        </Svg>
      );
    case 'chevron-right':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polyline points="9 18 15 12 9 6" {...s} />
        </Svg>
      );
    case 'chevron-down':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polyline points="6 9 12 15 18 9" {...s} />
        </Svg>
      );
    case 'chevron-up':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polyline points="18 15 12 9 6 15" {...s} />
        </Svg>
      );
    case 'heart':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path
            d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"
            {...s}
          />
        </Svg>
      );
    case 'star':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" {...s} />
        </Svg>
      );
    case 'bookmark':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" {...s} />
        </Svg>
      );
    case 'filter':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" {...s} />
        </Svg>
      );
    case 'bolt':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" {...s} />
        </Svg>
      );
    case 'phone':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path
            d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.37 1.9.72 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.35 1.85.59 2.81.72a2 2 0 011.72 2.01z"
            {...s}
          />
        </Svg>
      );
    case 'mail':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" {...s} />
          <Polyline points="22 6 12 13 2 6" {...s} />
        </Svg>
      );
    case 'lock':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Rect x={3} y={11} width={18} height={11} rx={2} ry={2} {...s} />
          <Path d="M7 11V7a5 5 0 0110 0v4" {...s} />
        </Svg>
      );
    case 'edit':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" {...s} />
          <Path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" {...s} />
        </Svg>
      );
    case 'menu':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Line x1={3} y1={12} x2={21} y2={12} {...s} />
          <Line x1={3} y1={6} x2={21} y2={6} {...s} />
          <Line x1={3} y1={18} x2={21} y2={18} {...s} />
        </Svg>
      );
    case 'chart':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Line x1={18} y1={20} x2={18} y2={10} {...s} />
          <Line x1={12} y1={20} x2={12} y2={4} {...s} />
          <Line x1={6} y1={20} x2={6} y2={14} {...s} />
        </Svg>
      );
    case 'globe':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Circle cx={12} cy={12} r={10} {...s} />
          <Line x1={2} y1={12} x2={22} y2={12} {...s} />
          <Path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" {...s} />
        </Svg>
      );
    case 'trash':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Polyline points="3 6 5 6 21 6" {...s} />
          <Path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" {...s} />
        </Svg>
      );
    case 'whatsapp':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path
            d="M20.52 3.48A11.78 11.78 0 0012.05.5C5.6.5.45 5.65.45 12.05c0 2.05.54 4.05 1.56 5.81L.25 23.5l5.79-1.71a11.6 11.6 0 005.99 1.63h.01c6.43 0 11.58-5.15 11.58-11.56a11.5 11.5 0 00-3.1-7.88zM12.05 21.4h-.01a9.45 9.45 0 01-4.84-1.33l-.35-.21-3.43 1.02 1.04-3.34-.23-.36a9.45 9.45 0 0114.59-12 9.43 9.43 0 012.78 6.71c0 5.23-4.27 9.51-9.55 9.51z"
            fill={color}
          />
        </Svg>
      );
    case 'camera':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" {...s} />
          <Circle cx={12} cy={13} r={4} {...s} />
        </Svg>
      );
    case 'cart':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Circle cx={9} cy={21} r={1} {...s} />
          <Circle cx={20} cy={21} r={1} {...s} />
          <Path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" {...s} />
        </Svg>
      );
    case 'message':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" {...s} />
        </Svg>
      );
    case 'settings':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Circle cx={12} cy={12} r={3} {...s} />
          <Path
            d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06a1.65 1.65 0 001.82.33h0a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51h0a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v0a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"
            {...s}
          />
        </Svg>
      );
    case 'logout':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" {...s} />
          <Polyline points="16 17 21 12 16 7" {...s} />
          <Line x1={21} y1={12} x2={9} y2={12} {...s} />
        </Svg>
      );
    case 'image':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Rect x={3} y={3} width={18} height={18} rx={2} ry={2} {...s} />
          <Circle cx={8.5} cy={8.5} r={1.5} {...s} />
          <Polyline points="21 15 16 10 5 21" {...s} />
        </Svg>
      );
    case 'clock':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Circle cx={12} cy={12} r={10} {...s} />
          <Polyline points="12 6 12 12 16 14" {...s} />
        </Svg>
      );
    case 'compass':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Circle cx={12} cy={12} r={10} {...s} />
          <Polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" {...s} />
        </Svg>
      );
    case 'shield':
      return (
        <Svg width={size} height={size} viewBox="0 0 24 24">
          <Path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" {...s} />
        </Svg>
      );
    default:
      return null;
  }
}
