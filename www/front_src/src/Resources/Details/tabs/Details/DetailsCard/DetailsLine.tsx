import * as React from 'react';

import { ParentSize } from '@visx/visx';

import { Typography, Box, makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  lineText: {
    fontSize: theme.typography.body2.fontSize,
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
}));

interface Props {
  line?: JSX.Element | string;
}

const DetailsLine = ({ line }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <ParentSize>
      {({ width }): JSX.Element => (
        <Typography component="div">
          <Box
            className={classes.lineText}
            lineHeight={1}
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
