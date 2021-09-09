import React from 'react';

import Slider from '.';

export default { title: 'Slider' };

export const normal = () => (
  <Slider
    images={[
      'https://res.cloudinary.com/dezez0fsn/image/upload/v1549874437/slider-default-image.png',
      'https://static.centreon.com/wp-content/uploads/2018/09/plugin-banner-it-operatio' +
        'ns-management.png',
      'https://s3.us-east-2.amazonaws.com/dzuz14/thumbnails/canyon.jpg',
      'https://s3.us-east-2.amazonaws.com/dzuz14/thumbnails/city.jpg',
      'https://s3.us-east-2.amazonaws.com/dzuz14/thumbnails/desert.jpg',
    ]}
  />
);
