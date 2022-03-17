import ExternalComponents from './models';

export const retrievedExternalComponents: ExternalComponents = {
  hooks: {
    '/bam/topcounter': {
      css: [],
      js: {
        bundle: './bundle.js',
        chunks: ['chunk.js'],
        commons: ['vendors.js', 'common.js'],
      },
    },
  },
  pages: {
    '/bam/page': {
      css: [],
      js: {
        bundle: './bundle.js',
        chunks: ['chunk.js'],
        commons: ['vendors.js', 'common.js'],
      },
    },
  },
};
