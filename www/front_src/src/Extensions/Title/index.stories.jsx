import React from 'react';

import Title from '.';

export default { title: 'Title' };

export const normal = () => <Title label="Test" />;

export const host = () => <Title label="Host" titleColor="host" />;

export const object = () => <Title icon="object" label="Test" />;

export const puzzle = () => (
  <Title icon="puzzle" label="Test" titleColor="blue" />
);
