import React from 'react';

import { MemoryRouter } from 'react-router-dom';

import Breadcrumb from '.';

export default { title: 'Breadcrumb' };

export const normal = () => (
  <MemoryRouter>
    <Breadcrumb
      breadcrumbs={[
        {
          label: 'first level',
          link: '#',
        },
        {
          label: 'second level',
          link: '#',
        },
        {
          label: 'third level',
          link: '#',
        },
      ]}
    />
  </MemoryRouter>
);
