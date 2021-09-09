/* eslint-disable no-undef */

import React from 'react';

import { render } from '@testing-library/react';

import Subtitle from '.';

const renderSubtitle = ({ label = '', subtitleType = '' }) =>
  render(<Subtitle label={label} subtitleType={subtitleType} />);

describe('Subtitle', () => {
  it('renders with given subtitleType style', () => {
    const subtitleType = 'subtitle-type';
    const { container } = renderSubtitle({ subtitleType });

    expect(container.firstChild).toHaveClass(subtitleType);
  });

  it('displays given label', () => {
    const label = 'label';

    const { getByText } = renderSubtitle({ label });

    expect(getByText(label)).toBeInTheDocument();
  });
});
