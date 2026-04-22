import colors from './colors.json' with { type: 'json' };

export const colorTokens = colors as {
  readonly positive: string;
  readonly warning: string;
  readonly risk: string;
};
