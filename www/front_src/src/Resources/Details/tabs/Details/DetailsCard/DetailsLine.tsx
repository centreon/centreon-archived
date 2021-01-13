import * as React from 'react';

import { ParentSize } from '@visx/visx';

import { Typography, Box, makeStyles } from '@material-ui/core';

const useStyles = makeStyles(() => ({
  lineText: {
    fontSize: 15,
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
}));

interface Props {
  line?: string;
}

const DetailsLine = ({ line }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <ParentSize>
      {({ width }): JSX.Element => (
        <Typography component="div">
          <Box
            fontWeight={500}
            lineHeight={1}
            className={classes.lineText}
            style={{
              maxWidth: width || 'unset',
            }}
          >
            {line}
          </Box>
        </Typography>
      )}
    </ParentSize>
  );
};

export default DetailsLine;
